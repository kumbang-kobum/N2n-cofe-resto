<?php

namespace Tests\Feature\Cashier;

use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeLine;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StockMove;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PosPaymentCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_consumes_recipe_stock_once_and_records_cogs(): void
    {
        $this->withoutMiddleware();

        $user = User::create([
            'name' => 'Cashier Test',
            'email' => 'cashier-test@example.test',
            'password' => Hash::make('password'),
        ]);

        $gram = Unit::create(['name' => 'Gram', 'symbol' => 'g']);
        $kilogram = Unit::create(['name' => 'Kilogram', 'symbol' => 'kg']);
        UnitConversion::create([
            'from_unit_id' => $gram->id,
            'to_unit_id' => $kilogram->id,
            'multiplier' => 0.001,
        ]);

        $item = Item::create([
            'name' => 'Kopi Blend',
            'base_unit_id' => $kilogram->id,
            'track_expiry' => true,
            'is_active' => true,
        ]);

        $batch = ItemBatch::create([
            'item_id' => $item->id,
            'received_at' => '2026-05-04 08:00:00',
            'expired_at' => '2026-12-31',
            'qty_on_hand_base' => 1,
            'unit_cost_base' => 200000,
            'status' => 'ACTIVE',
        ]);

        $product = Product::create([
            'name' => 'Es Kopi',
            'price_default' => 10000,
            'menu_type' => Product::TYPE_DRINK,
            'is_active' => true,
        ]);
        $recipe = Recipe::create(['product_id' => $product->id]);
        RecipeLine::create([
            'recipe_id' => $recipe->id,
            'item_id' => $item->id,
            'qty' => 10,
            'unit_id' => $gram->id,
        ]);

        $sale = Sale::create([
            'receipt_no' => 'NT/04/05/2026/000001',
            'status' => 'DRAFT',
            'cashier_id' => $user->id,
            'total' => 10000,
        ]);
        SaleLine::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 10000,
        ]);

        $this->actingAs($user)->post(route('cashier.pos.pay'), [
            'sale_id' => $sale->id,
            'payment_method' => 'CASH',
            'paid_amount' => '11.000',
            'discount_amount' => 0,
        ])->assertRedirect(route('cashier.pos.receipt', $sale->id));

        $sale->refresh();
        $batch->refresh();

        $this->assertSame('PAID', $sale->status);
        $this->assertSame(10000.0, (float) $sale->total);
        $this->assertSame(1000.0, (float) $sale->tax_amount);
        $this->assertSame(11000.0, (float) $sale->grand_total);
        $this->assertSame(2000.0, (float) $sale->cogs_total);
        $this->assertEqualsWithDelta(0.99, (float) $batch->qty_on_hand_base, 0.000001);
        $this->assertSame(1, StockMove::where('type', 'CONSUMPTION')->count());

        $this->actingAs($user)->post(route('cashier.pos.pay'), [
            'sale_id' => $sale->id,
            'payment_method' => 'CASH',
            'paid_amount' => '11.000',
            'discount_amount' => 0,
        ])->assertStatus(400);

        $this->assertSame(1, StockMove::where('type', 'CONSUMPTION')->count());
        $this->assertEqualsWithDelta(0.99, (float) $batch->fresh()->qty_on_hand_base, 0.000001);
    }
}
