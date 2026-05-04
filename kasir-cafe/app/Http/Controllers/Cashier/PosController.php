<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StockMove;
use App\Models\ItemBatch;
use App\Models\Item;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Exceptions\InsufficientStockException;
use App\Services\ProductConsumptionService;
use App\Services\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PosController extends Controller
{
    protected function estimateProductCost(Product $product, Collection $batchesByItem, UnitConverter $converter): ?float
    {
        $recipe = $product->recipe;

        if (! $recipe || $recipe->lines->isEmpty()) {
            return null;
        }

        $cost = 0.0;

        foreach ($recipe->lines as $line) {
            $item = $line->item;
            if (! $item || ! $item->base_unit_id) {
                return null;
            }

            $needBase = $converter->toBase(
                (float) $line->qty,
                (int) $line->unit_id,
                (int) $item->base_unit_id
            );

            $remaining = $needBase;
            $itemCost = 0.0;
            $batches = $batchesByItem->get($item->id, collect());

            foreach ($batches as $batch) {
                if ($remaining <= 0.000001) {
                    break;
                }

                $available = (float) $batch->qty_on_hand_base;
                if ($available <= 0) {
                    continue;
                }

                $take = min($remaining, $available);
                $itemCost += $take * (float) $batch->unit_cost_base;
                $remaining -= $take;
            }

            if ($remaining > 0.000001) {
                return null;
            }

            $cost += $itemCost;
        }

        return $cost;
    }

    protected function resolveTaxRate(): float
    {
        $setting = Setting::first();

        if ($setting && $setting->tax_enabled === false) {
            return 0.0;
        }

        return (float) config('pos.tax_rate', 0.10);
    }

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

        // Jika belum ada transaksi aktif sama sekali, siapkan draft baru otomatis
        if (! $sale) {
            $sale = Sale::create([
                'receipt_no' => $this->generateReceiptNo(),
                'status' => 'DRAFT',
                'cashier_id' => auth()->id(),
                'total' => 0,
            ]);

            $sale->load('lines.product');
        }

        $openQuery = trim((string) $request->get('open_q', ''));

        $openSalesQuery = Sale::with(['lines.product', 'cashier'])
            ->where('status', 'OPEN');

        if (! auth()->user()?->hasRole('admin')) {
            $openSalesQuery->where('cashier_id', auth()->id());
        }

        if ($openQuery !== '') {
            $openSalesQuery->where(function ($q) use ($openQuery) {
                $q->where('customer_name', 'like', '%' . $openQuery . '%')
                    ->orWhere('table_no', 'like', '%' . $openQuery . '%')
                    ->orWhere('id', $openQuery);
            });
        }

        $openSales = $openSalesQuery
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

        $setting = Setting::first();
        $lowMarginWarningEnabled = (bool) ($setting?->pos_low_margin_warning_enabled ?? false);

        // Warning stok kosong (berdasarkan resep untuk 1 porsi)
        $today = now()->toDateString();
        $stockByItem = ItemBatch::query()
            ->selectRaw('item_id, SUM(qty_on_hand_base) as qty')
            ->where('status', 'ACTIVE')
            ->where('qty_on_hand_base', '>', 0)
            ->whereDate('expired_at', '>=', $today)
            ->groupBy('item_id')
            ->pluck('qty', 'item_id');

        $batchesByItem = ItemBatch::query()
            ->where('status', 'ACTIVE')
            ->where('qty_on_hand_base', '>', 0)
            ->whereDate('expired_at', '>=', $today)
            ->orderBy('expired_at')
            ->orderBy('received_at')
            ->get()
            ->groupBy('item_id');

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
            $product->estimated_cost = null;
            $product->low_margin_warning = null;

            if ($lowMarginWarningEnabled) {
                try {
                    $estimatedCost = $this->estimateProductCost($product, $batchesByItem, $converter);
                } catch (\Throwable $e) {
                    $estimatedCost = null;
                }

                if ($estimatedCost !== null) {
                    $product->estimated_cost = round($estimatedCost, 2);

                    if ((float) $product->price_default <= $estimatedCost + 0.000001) {
                        $cmp = abs((float) $product->price_default - $estimatedCost) <= 0.000001
                            ? 'sama dengan'
                            : 'lebih rendah dari';

                        $product->low_margin_warning = 'Harga jual menu ini ' . $cmp . ' estimasi modal per porsi.';
                    }
                }
            }
        }

        return view('cashier.pos', [
            'sale'     => $sale,
            'products' => $products,
            'search'   => $search,
            'openSales' => $openSales,
            'openQuery' => $openQuery,
            'lowMarginWarningEnabled' => $lowMarginWarningEnabled,
            'taxRate' => $this->resolveTaxRate(),
        ]);
    }

    /**
     * Buat transaksi DRAFT baru.
     */
    public function newSale()
    {
        DB::transaction(function () {
            $drafts = Sale::where('cashier_id', auth()->id())
                ->where('status', 'DRAFT')
                ->pluck('id');

            if ($drafts->isNotEmpty()) {
                SaleLine::whereIn('sale_id', $drafts)->delete();
                Sale::whereIn('id', $drafts)->delete();
            }
        });

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
        $this->normalizeNumericInputs($request, ['qty']);

        $request->validate([
            'sale_id'    => ['required', 'exists:sales,id'],
            'product_id' => ['required', 'exists:products,id'],
            'qty'        => ['required', 'numeric', 'gt:0'],
            'table_no' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['nullable', 'string', 'max:100'],
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

        // Simpan info meja/nama (jika ada)
        $tableNo = trim((string) $request->input('table_no'));
        $customerName = trim((string) $request->input('customer_name'));
        if ($tableNo !== '' || $customerName !== '') {
            $sale->table_no = $tableNo !== '' ? $tableNo : $sale->table_no;
            $sale->customer_name = $customerName !== '' ? $customerName : $sale->customer_name;
            $sale->save();
        }

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
        $this->normalizeNumericInputs($request, ['qty']);

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
    public function pay(Request $request, ProductConsumptionService $consumptionService)
    {
        $this->normalizeNumericInputs($request, ['discount_amount', 'paid_amount']);

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

        $saleId = (int) $request->sale_id;

        try {
            DB::transaction(function () use ($request, $saleId, $consumptionService) {
                /** @var Sale $sale */
                $sale = Sale::whereKey($saleId)
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_if(! in_array($sale->status, ['DRAFT', 'OPEN'], true), 400, 'Transaksi sudah dibayar.');

                $sale->load('lines.product.recipe.lines.item');

                if ($sale->lines->isEmpty()) {
                    throw ValidationException::withMessages([
                        'sale_id' => 'Keranjang masih kosong.',
                    ]);
                }

                $consumption = $consumptionService->consumeProductLines(
                    $sale->lines,
                    'sale',
                    (int) $sale->id,
                    'POS #' . $sale->id
                );
                $cogs = (float) $consumption['cogs'];
                $consumedItems = $consumption['items'];

                // Update status & ringkasan keuangan sale
                $taxRate = $this->resolveTaxRate();
                $discount = (float) $request->input('discount_amount', 0);
                if ($discount < 0) {
                    $discount = 0;
                }
                if ($discount > (float) $sale->total) {
                    $discount = (float) $sale->total;
                }

                $taxBase = max(0, (float) $sale->total - $discount);
                $taxAmount = round($taxBase * $taxRate, 0);
                $grandTotal = round($taxBase + $taxAmount, 0);

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
                $sale->profit_gross   = $taxBase - $cogs;
                $sale->save();

                if (! empty($consumedItems)) {
                    AuditLog::log(auth()->id(), 'STOCK_CONSUMED', $sale, [
                        'sale_id' => $sale->id,
                        'items' => $consumedItems,
                    ]);
                }
            });
        } catch (InsufficientStockException $e) {
            $item = Item::with('baseUnit')->find($e->itemId);
            $unitName = $item?->baseUnit?->name ?? 'unit';
            $itemName = $item?->name ?? ('Item #' . $e->itemId);
            $shortage = number_format((float) $e->shortageBase, 2, ',', '.');

            throw ValidationException::withMessages([
                'stock' => "Stok {$itemName} tidak cukup. Kurang {$shortage} {$unitName}.",
            ]);
        }

        return redirect()
            ->route('cashier.pos.receipt', $saleId)
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

        if (
            $sale->cashier_id !== auth()->id()
            && ! auth()->user()?->hasAnyRole(['admin', 'manager'])
        ) {
            abort(403);
        }

        return view('cashier.receipt', compact('sale'));
    }
}
