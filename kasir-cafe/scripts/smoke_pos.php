<?php

use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeLine;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use App\Services\FefoAllocator;
use App\Services\UnitConverter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "== POS Smoke Test ==\n";

DB::beginTransaction();

try {
    $role = Role::firstOrCreate(['name' => 'cashier']);

    $user = User::firstOrCreate(
        ['email' => 'smoke_cashier@demo.local'],
        [
            'name' => 'Smoke Cashier',
            'password' => Hash::make('password'),
        ]
    );
    $user->syncRoles([$role->name]);

    Auth::login($user);

    $unitGram = Unit::firstOrCreate(['name' => 'Gram'], ['symbol' => 'g']);
    $unitKg = Unit::firstOrCreate(['name' => 'Kilogram'], ['symbol' => 'kg']);

    UnitConversion::updateOrCreate(
        ['from_unit_id' => $unitGram->id, 'to_unit_id' => $unitKg->id],
        ['multiplier' => 0.001]
    );
    UnitConversion::updateOrCreate(
        ['from_unit_id' => $unitKg->id, 'to_unit_id' => $unitGram->id],
        ['multiplier' => 1000]
    );

    $item = Item::firstOrCreate(
        ['name' => 'SMOKE Kopi'],
        [
            'base_unit_id' => $unitGram->id,
            'track_expiry' => 1,
            'min_stock' => 0,
            'is_active' => 1,
        ]
    );

    $batch = ItemBatch::create([
        'item_id' => $item->id,
        'received_at' => now(),
        'expired_at' => now()->addDays(30)->toDateString(),
        'qty_on_hand_base' => 1000,
        'unit_cost_base' => 10,
        'status' => 'ACTIVE',
    ]);

    $product = Product::firstOrCreate(
        ['name' => 'SMOKE Kopi Hitam'],
        ['price_default' => 10000, 'is_active' => 1]
    );

    $recipe = Recipe::firstOrCreate(['product_id' => $product->id]);

    RecipeLine::updateOrCreate(
        ['recipe_id' => $recipe->id, 'item_id' => $item->id],
        ['qty' => 10, 'unit_id' => $unitGram->id]
    );

    $sale = Sale::create([
        'receipt_no' => null,
        'status' => 'DRAFT',
        'cashier_id' => $user->id,
        'total' => 0,
    ]);

    SaleLine::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => 10000,
    ]);

    $sale->update([
        'total' => SaleLine::where('sale_id', $sale->id)->sum(DB::raw('qty * price')),
    ]);

    $before = (float) $batch->fresh()->qty_on_hand_base;

    $allocator = app(FefoAllocator::class);
    $converter = app(UnitConverter::class);

    // Pay logic (simplified) using same services
    $needs = [];
    foreach ($sale->lines()->with('product.recipe.lines.item')->get() as $line) {
        $recipe = $line->product?->recipe;
        foreach ($recipe->lines as $detail) {
            $itemId = $detail->item_id;
            $qtyBase = $converter->toBase(
                (float) $detail->qty,
                (int) $detail->unit_id,
                (int) $detail->item->base_unit_id
            ) * (float) $line->qty;
            $needs[$itemId] = ($needs[$itemId] ?? 0) + $qtyBase;
        }
    }

    foreach ($needs as $itemId => $needBase) {
        $allocs = $allocator->allocate($itemId, $needBase);
        foreach ($allocs as $alloc) {
            $b = $alloc['batch'];
            $take = (float) $alloc['take'];
            $b->qty_on_hand_base = max(0, (float) $b->qty_on_hand_base - $take);
            if ($b->qty_on_hand_base <= 0.000001) {
                $b->qty_on_hand_base = 0;
                $b->status = 'DEPLETED';
            }
            $b->save();
        }
    }

    $after = (float) $batch->fresh()->qty_on_hand_base;

    if (abs(($before - 20) - $after) > 0.000001) {
        throw new RuntimeException("Stok tidak berkurang sesuai. Before={$before} After={$after}");
    }

    echo "OK: stok berkurang 20 gram.\n";

    DB::rollBack();
    echo "Rollback: data uji dibersihkan.\n";
} catch (Throwable $e) {
    DB::rollBack();
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
