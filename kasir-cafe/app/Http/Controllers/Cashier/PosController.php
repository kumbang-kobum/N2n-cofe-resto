<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StockMove;
use App\Models\ItemBatch;
use App\Models\AuditLog;
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
                ->whereIn('status', ['DRAFT', 'OPEN'])
                ->orderByDesc('id')
                ->first();
        }

        $openSales = Sale::with('lines.product')
            ->where('cashier_id', auth()->id())
            ->whereIn('status', ['DRAFT', 'OPEN'])
            ->orderByDesc('updated_at')
            ->get();

        // Pencarian katalog
        $search = trim((string) $request->get('q', ''));

        $productsQuery = Product::query()
            ->where('is_active', true);

        if ($search !== '') {
            $productsQuery->where('name', 'like', '%' . $search . '%');
        }

        $products = $productsQuery
            ->with(['recipe.lines.item'])
            ->orderBy('name')
            ->get();

        // Warning stok kosong (berdasarkan resep untuk 1 porsi)
        $today = now()->toDateString();
        $stockByItem = ItemBatch::query()
            ->selectRaw('item_id, SUM(qty_on_hand_base) as qty')
            ->where('status', 'ACTIVE')
            ->where('qty_on_hand_base', '>', 0)
            ->whereDate('expired_at', '>=', $today)
            ->groupBy('item_id')
            ->pluck('qty', 'item_id');

        $converter = app(UnitConverter::class);

        foreach ($products as $product) {
            $warning = null;
            $recipe = $product->recipe;

            if (! $recipe || $recipe->lines->isEmpty()) {
                $warning = 'Resep belum diatur';
            } else {
                foreach ($recipe->lines as $line) {
                    $item = $line->item;
                    if (! $item || ! $item->base_unit_id) {
                        $warning = 'Item/base unit tidak valid';
                        break;
                    }

                    try {
                        $needBase = $converter->toBase(
                            (float) $line->qty,
                            (int) $line->unit_id,
                            (int) $item->base_unit_id
                        );
                    } catch (\Throwable $e) {
                        $warning = 'Konversi unit belum diset';
                        break;
                    }

                    $available = (float) ($stockByItem[$item->id] ?? 0);
                    if ($available + 0.000001 < $needBase) {
                        $warning = 'Stok kosong/kurang';
                        break;
                    }

                    if ($warning === null && $item->min_stock !== null && $available <= (float) $item->min_stock) {
                        $warning = 'Stok menipis';
                    }
                }
            }

            $product->stock_warning = $warning;
        }

        return view('cashier.pos', [
            'sale'     => $sale,
            'products' => $products,
            'search'   => $search,
            'openSales' => $openSales,
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

        // Hanya boleh edit DRAFT/OPEN
        if (! in_array($sale->status, ['DRAFT', 'OPEN'], true)) {
            abort(400, 'Hanya transaksi DRAFT/OPEN yang bisa diubah.');
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
     * Update qty item di keranjang.
     */
    public function updateLine(Request $request, SaleLine $line)
    {
        $request->validate([
            'qty' => ['required', 'numeric', 'gt:0'],
        ]);

        $sale = Sale::where('cashier_id', auth()->id())
            ->whereIn('status', ['DRAFT', 'OPEN'])
            ->findOrFail($line->sale_id);

        $line->qty = (float) $request->qty;
        $line->save();

        $sale->update([
            'total' => SaleLine::where('sale_id', $sale->id)
                ->sum(DB::raw('qty * price')),
        ]);

        return redirect()->route('cashier.pos', ['sale_id' => $sale->id]);
    }

    /**
     * Hapus item dari keranjang.
     */
    public function deleteLine(SaleLine $line)
    {
        $sale = Sale::where('cashier_id', auth()->id())
            ->whereIn('status', ['DRAFT', 'OPEN'])
            ->findOrFail($line->sale_id);

        $line->delete();

        $sale->update([
            'total' => SaleLine::where('sale_id', $sale->id)
                ->sum(DB::raw('qty * price')),
        ]);

        return redirect()->route('cashier.pos', ['sale_id' => $sale->id]);
    }

    /**
     * Kosongkan keranjang.
     */
    public function clearCart(Request $request)
    {
        $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
        ]);

        $sale = Sale::where('cashier_id', auth()->id())
            ->whereIn('status', ['DRAFT', 'OPEN'])
            ->findOrFail($request->sale_id);

        SaleLine::where('sale_id', $sale->id)->delete();

        $sale->update([
            'total' => 0,
        ]);

        return redirect()->route('cashier.pos', ['sale_id' => $sale->id])
            ->with('status', 'Keranjang dikosongkan.');
    }

    /**
     * Batalkan transaksi DRAFT/OPEN.
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
        ]);

        $sale = Sale::where('cashier_id', auth()->id())
            ->whereIn('status', ['DRAFT', 'OPEN'])
            ->findOrFail($request->sale_id);

        $sale->status = 'CANCELLED';
        $sale->save();

        AuditLog::log(auth()->id(), 'SALE_CANCELLED', $sale, [
            'sale_id' => $sale->id,
        ]);

        return redirect()
            ->route('cashier.pos')
            ->with('status', 'Transaksi dibatalkan.');
    }

    /**
     * Bayar transaksi + FEFO konsumsi bahan resep.
     */
    public function pay(Request $request, FefoAllocator $allocator, UnitConverter $converter)
    {
        $request->merge([
            'payment_method' => strtoupper((string) $request->input('payment_method')),
        ]);

        $request->validate([
            'sale_id'        => ['required', 'exists:sales,id'],
            'payment_method' => ['required', 'in:CASH,QRIS,DEBIT'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'table_no' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['nullable', 'string', 'max:100'],
        ]);

        $sale = Sale::with([
            'lines.product.recipe.lines.item',
        ])->findOrFail($request->sale_id);

        abort_if(! in_array($sale->status, ['DRAFT', 'OPEN'], true), 400, 'Transaksi sudah dibayar.');

        DB::transaction(function () use ($request, $sale, $allocator, $converter) {

            // Kumpulkan kebutuhan bahan (dalam base unit) dari semua recipe
            $needs = [];
            foreach ($sale->lines as $line) {
                $product = $line->product;
                $recipe = $product?->recipe;
                if (! $recipe || $recipe->lines->isEmpty()) {
                    throw ValidationException::withMessages([
                        'recipe' => 'Resep belum diatur untuk menu: ' . ($product->name ?? 'Unknown'),
                    ]);
                }

                foreach ($recipe->lines as $detail) {
                    $item = $detail->item;
                    if (! $item || ! $item->base_unit_id) {
                        throw ValidationException::withMessages([
                            'recipe' => 'Item/base unit tidak valid pada resep menu: ' . ($product->name ?? 'Unknown'),
                        ]);
                    }

                    $itemId = $item->id;
                    // Konversi qty resep ke base unit item, lalu kalikan jumlah pesanan
                    $qtyBase = $converter->toBase(
                        (float) $detail->qty,
                        (int) $detail->unit_id,
                        (int) $item->base_unit_id
                    ) * (float) $line->qty;

                    if (! isset($needs[$itemId])) {
                        $needs[$itemId] = 0;
                    }
                    $needs[$itemId] += $qtyBase;
                }
            }

        $cogs = 0;
        $consumedItems = [];

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

            $consumedItems[] = [
                'item_id' => $itemId,
                'qty_base' => (float) $needBase,
            ];
        }

            // Update status & ringkasan keuangan sale
            $taxRate = (float) config('pos.tax_rate', 0.10);
            $discount = (float) $request->input('discount_amount', 0);
            if ($discount < 0) {
                $discount = 0;
            }
            if ($discount > (float) $sale->total) {
                $discount = (float) $sale->total;
            }

            $taxBase = max(0, (float) $sale->total - $discount);
            $taxAmount = round($taxBase * $taxRate, 2);
            $grandTotal = $taxBase + $taxAmount;

            $paidAmount = (float) $request->input('paid_amount', 0);
            if ($paidAmount < $grandTotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Uang dibayar kurang dari total.',
                ]);
            }
            $changeAmount = $paidAmount - $grandTotal;

            $sale->table_no = trim((string) $request->input('table_no')) ?: $sale->table_no;
            $sale->customer_name = trim((string) $request->input('customer_name')) ?: $sale->customer_name;
            $sale->status         = 'PAID';
            $sale->payment_method = $request->payment_method;
            $sale->paid_at        = now();
            $sale->cogs_total     = $cogs;
            $sale->discount_amount = $discount;
            $sale->tax_rate       = $taxRate;
            $sale->tax_amount     = $taxAmount;
            $sale->grand_total    = $grandTotal;
            $sale->paid_amount    = $paidAmount;
            $sale->change_amount  = $changeAmount;
        $sale->profit_gross   = max(0, $taxBase) - $cogs;
        $sale->save();

        if (! empty($consumedItems)) {
            AuditLog::log(auth()->id(), 'STOCK_CONSUMED', $sale, [
                'sale_id' => $sale->id,
                'items' => $consumedItems,
            ]);
        }
    });

        return redirect()
            ->route('cashier.pos.receipt', $sale->id)
            ->with('status', 'Pembayaran berhasil.');
    }

    /**
     * Tahan transaksi (Open Bill) untuk dibayar nanti.
     */
    public function hold(Request $request)
    {
        $data = $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'table_no' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['nullable', 'string', 'max:100'],
        ]);

        $sale = Sale::where('cashier_id', auth()->id())->findOrFail($data['sale_id']);

        if (! in_array($sale->status, ['DRAFT', 'OPEN'], true)) {
            abort(400, 'Hanya transaksi DRAFT/OPEN yang bisa ditahan.');
        }

        $sale->table_no = trim((string) ($data['table_no'] ?? '')) ?: null;
        $sale->customer_name = trim((string) ($data['customer_name'] ?? '')) ?: null;
        $sale->status = 'OPEN';
        $sale->save();

        return redirect()
            ->route('cashier.pos')
            ->with('status', 'Transaksi ditahan (Open Bill).');
    }

    /**
     * Nota pembayaran (thermal 80mm).
     */
    public function receipt(int $saleId)
    {
        $sale = Sale::with(['lines.product', 'cashier'])->findOrFail($saleId);

        if ($sale->cashier_id !== auth()->id() && ! auth()->user()?->hasRole('admin')) {
            abort(403);
        }

        return view('cashier.receipt', compact('sale'));
    }
}
