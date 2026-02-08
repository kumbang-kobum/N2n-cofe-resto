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

class PosController extends Controller
{
    /**
     * Halaman utama POS kasir.
     */
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
            'status'     => 'DRAFT',
            'cashier_id' => auth()->id(),
            'total'      => 0,
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
    public function pay(
        Request $request,
        FefoAllocator $allocator,
        UnitConverter $converter
    ) {
        $request->validate([
            'sale_id'        => ['required', 'exists:sales,id'],
            'payment_method' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request, $allocator, $converter) {

            /** @var Sale $sale */
            $sale = Sale::with('lines.product.recipe.lines.item')
                ->where('cashier_id', auth()->id())
                ->lockForUpdate()
                ->findOrFail($request->sale_id);

            if ($sale->status !== 'DRAFT') {
                abort(400, 'Transaksi bukan DRAFT.');
            }

            // Kumpulkan kebutuhan bahan (dalam base unit)
            $needs = [];

            foreach ($sale->lines as $line) {
                $product = $line->product;

                // Kalau belum ada resep, lewati (tidak kurangi stok bahan)
                if (!$product->recipe) {
                    continue;
                }

                foreach ($product->recipe->lines as $rl) {
                    $item = $rl->item;

                    $qtyNeeded = (float) $rl->qty * (float) $line->qty;

                    $qtyBase = $converter->toBase(
                        $qtyNeeded,
                        $rl->unit_id,
                        $item->base_unit_id
                    );

                    if (!isset($needs[$item->id])) {
                        $needs[$item->id] = 0.0;
                    }

                    $needs[$item->id] += $qtyBase;
                }
            }

            $cogs = 0.0;

            // FEFO konsumsi stok tiap bahan
            foreach ($needs as $itemId => $qtyBase) {

                $result   = $allocator->allocate($itemId, $qtyBase);
                $unfilled = isset($result['unfilled']) ? (float) $result['unfilled'] : 0.0;

                if ($unfilled > 0.000001) {
                    // stok kurang
                    throw ValidationException::withMessages([
                        'sale_id' => 'Stok tidak cukup untuk salah satu bahan.',
                    ]);
                }

                if (!empty($result['lines'])) {
                    foreach ($result['lines'] as $a) {
                        /** @var ItemBatch $batch */
                        $batch = ItemBatch::lockForUpdate()->findOrFail($a['batch_id']);
                        $take  = (float) $a['qty_base'];

                        // Kurangi stok batch
                        $batch->qty_on_hand_base = (float) $batch->qty_on_hand_base - $take;

                        if ($batch->qty_on_hand_base <= 0.000001) {
                            $batch->qty_on_hand_base = 0;
                            $batch->status = 'DEPLETED';
                        }

                        $batch->save();

                        // Catat pergerakan stok
                        StockMove::create([
                            'moved_at'   => now(),
                            'item_id'    => $itemId,
                            'batch_id'   => $batch->id,
                            'qty_base'   => -$take,
                            'type'       => 'CONSUMPTION',   // pastikan cocok dengan enum di DB
                            'ref_type'   => 'sale',
                            'ref_id'     => $sale->id,
                            'created_by' => auth()->id(),
                            'note'       => 'POS #' . $sale->id,
                        ]);

                        $cogs += $take * (float) $batch->unit_cost_base;
                    }
                }
            }

            // Update status transaksi
            $sale->update([
                'status'        => 'PAID',
                'paid_at'       => now(),
                'payment_method'=> $request->payment_method,
                'cogs_total'    => round($cogs, 2),
                'profit_gross'  => round($sale->total - $cogs, 2),
            ]);
        });

        return redirect()
            ->route('cashier.pos')
            ->with('status', 'Pembayaran berhasil.');
    }
}