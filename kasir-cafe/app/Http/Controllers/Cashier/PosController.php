<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StockMove;
use App\Models\ItemBatch;
use App\Services\FefoAllocator;
use App\Services\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PosController extends Controller
{
    /**
     * Halaman utama POS kasir.
     */
    protected function generateReceiptNo(): string
    {
        $today = Carbon::today();

        // Cari nota terakhir di hari ini
        $last = Sale::whereDate('created_at', $today->toDateString())
            ->whereNotNull('receipt_no')
            ->orderByDesc('id')
            ->first();

        $seq = 1;

        if ($last && $last->receipt_no) {
            // Format: NT/dd/mm/YYYY/000001  → ambil angka terakhir
            $parts = explode('/', $last->receipt_no);
            $lastNumber = (int) end($parts);
            $seq = $lastNumber + 1;
        }

        $number = str_pad($seq, 6, '0', STR_PAD_LEFT);

        return 'NT/' . $today->format('d/m/Y') . '/' . $number;
    }

    public function index(Request $request)
    {
        $sale = null;

        // Jika ada ?sale_id di query, coba ambil sale itu
        if ($request->filled('sale_id')) {
            $sale = Sale::with('lines.product')
                ->where('cashier_id', auth()->id())
                ->find($request->sale_id);
        }

        // Kalau belum ada, ambil DRAFT terakhir milik kasir
        if (!$sale) {
            $sale = Sale::with('lines.product')
                ->where('cashier_id', auth()->id())
                ->where('status', 'DRAFT')
                ->orderByDesc('id')
                ->first();
        }

        // Pencarian katalog
        $search = trim((string) $request->get('q', ''));

        $productsQuery = Product::query()
            ->where('is_active', true);

        if ($search !== '') {
            $productsQuery->where('name', 'like', '%' . $search . '%');
        }

        $products = $productsQuery
            ->orderBy('name')
            ->get();

        return view('cashier.pos', [
            'sale'     => $sale,
            'products' => $products,
            'search'   => $search,
        ]);
    }

    /**
     * Buat transaksi DRAFT baru.
     */
    public function newSale()
    {
        $sale = Sale::create([
            'receipt_no' => $this->generateReceiptNo(),
            'status' => 'DRAFT',
            'cashier_id' => auth()->id(),
            'total' => 0,
        ]);

        return redirect()
            ->route('cashier.pos', ['sale_id' => $sale->id])
            ->with('status', 'Transaksi baru siap.');
    }

    /**
     * Tambah menu ke transaksi berjalan.
     */
    public function addLine(Request $request)
    {
        $request->validate([
            'sale_id'    => ['required', 'exists:sales,id'],
            'product_id' => ['required', 'exists:products,id'],
            'qty'        => ['required', 'numeric', 'gt:0'],
        ]);

        /** @var Sale $sale */
        $sale = Sale::with('lines')
            ->where('cashier_id', auth()->id())
            ->findOrFail($request->sale_id);

        // Hanya boleh edit DRAFT
        if ($sale->status !== 'DRAFT') {
            abort(400, 'Hanya transaksi DRAFT yang bisa diubah.');
        }

        $product = Product::where('is_active', true)
            ->findOrFail($request->product_id);

        // Tambah line
        SaleLine::create([
            'sale_id'    => $sale->id,
            'product_id' => $product->id,
            'qty'        => (float) $request->qty,
            'price'      => (float) $product->price_default,
        ]);

        // Re-hitung total
        $sale->update([
            'total' => SaleLine::where('sale_id', $sale->id)
                ->sum(DB::raw('qty * price')),
        ]);

        return redirect()->route('cashier.pos', ['sale_id' => $sale->id]);
    }

    /**
     * Bayar transaksi + FEFO konsumsi bahan resep.
     */
    public function pay(Request $request, FefoAllocator $allocator)
{
    $request->validate([
        'sale_id'        => ['required', 'exists:sales,id'],
        'payment_method' => ['required', 'in:CASH,QRIS,DEBIT'],
    ]);

    $sale = Sale::with([
        'lines.menu',
        'lines.recipe.details.item',
    ])->findOrFail($request->sale_id);

    abort_if($sale->status !== 'DRAFT', 400, 'Transaksi sudah dibayar.');

    DB::transaction(function () use ($request, $sale, $allocator) {

        // Kumpulkan kebutuhan bahan (dalam base unit) dari semua recipe
        $needs = [];
        foreach ($sale->lines as $line) {
            $recipe = $line->recipe;
            if (! $recipe) {
                continue;
            }

            foreach ($recipe->details as $detail) {
                $itemId  = $detail->item_id;
                // qty_base di detail sudah dalam base unit
                $qtyBase = (float) $detail->qty_base * (float) $line->qty;

                if (! isset($needs[$itemId])) {
                    $needs[$itemId] = 0;
                }
                $needs[$itemId] += $qtyBase;
            }
        }

        $cogs = 0;

        // Alokasikan dari batch FEFO dan kurangi stok
        foreach ($needs as $itemId => $needBase) {
            // FefoAllocator sekarang mengembalikan array of ['batch' => ItemBatch, 'take' => float]
            $allocs = $allocator->allocate($itemId, $needBase);

            $takenTotal = 0;

            foreach ($allocs as $alloc) {
                /** @var \App\Models\ItemBatch $batch */
                $batch = $alloc['batch'];
                $take  = (float) $alloc['take'];

                if ($take <= 0) {
                    continue;
                }

                $takenTotal += $take;

                // Kurangi stok batch
                $batch->qty_on_hand_base = max(0, (float) $batch->qty_on_hand_base - $take);
                if ($batch->qty_on_hand_base <= 0.000001) {
                    $batch->qty_on_hand_base = 0;
                    $batch->status = 'DEPLETED';
                }
                $batch->save();

                // Catat pergerakan stok (keluar untuk penjualan)
                StockMove::create([
                    'moved_at'   => now(),          // boleh diganti $sale->sale_date kalau mau
                    'item_id'    => $itemId,
                    'batch_id'   => $batch->id,
                    'qty_base'   => -$take,
                    'type'       => 'CONSUMPTION',  // pastikan enum di DB sama
                    'ref_type'   => 'sale',
                    'ref_id'     => $sale->id,
                    'created_by' => auth()->id(),
                    'note'       => 'POS #' . $sale->id,
                ]);

                // Tambah COGS
                $cogs += $take * (float) $batch->unit_cost_base;
            }

            // Safety check: pastikan stok yang diambil sama dengan kebutuhan
            if (abs($takenTotal - $needBase) > 0.000001) {
                throw new \RuntimeException('Stok tidak cukup untuk item id: ' . $itemId);
            }
        }

        // Update status & ringkasan keuangan sale
        $sale->status         = 'PAID';
        $sale->payment_method = $request->payment_method;
        $sale->paid_at        = now();
        $sale->cogs_total     = $cogs;
        $sale->profit_total   = $sale->grand_total - $cogs;
        $sale->save();
    });

    return redirect()
        ->route('cashier.pos')
        ->with('status', 'Pembayaran berhasil.');
}
}