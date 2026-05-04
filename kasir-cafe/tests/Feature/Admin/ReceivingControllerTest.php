<?php

namespace Tests\Feature\Admin;

use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\StockMove;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReceivingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_renders_without_blade_compile_errors(): void
    {
        $this->withoutMiddleware();

        $user = $this->createUser();
        $kilogram = Unit::create(['name' => 'Kilogram', 'symbol' => 'kg']);

        Item::create([
            'name' => 'Kopi Blend',
            'base_unit_id' => $kilogram->id,
            'track_expiry' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.receivings.create'))
            ->assertOk()
            ->assertSee('Terima Stok')
            ->assertSee('Kopi Blend');
    }

    public function test_receiving_keeps_input_qty_and_converts_stock_to_base_unit(): void
    {
        $this->withoutMiddleware();

        $user = $this->createUser();
        $gram = Unit::create(['name' => 'Gram', 'symbol' => 'g']);
        $kilogram = Unit::create(['name' => 'Kilogram', 'symbol' => 'kg']);

        UnitConversion::create([
            'from_unit_id' => $gram->id,
            'to_unit_id' => $kilogram->id,
            'multiplier' => 0.001,
        ]);
        UnitConversion::create([
            'from_unit_id' => $kilogram->id,
            'to_unit_id' => $gram->id,
            'multiplier' => 1000,
        ]);

        $item = Item::create([
            'name' => 'Kopi Blend',
            'base_unit_id' => $kilogram->id,
            'track_expiry' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('admin.receivings.store'), [
            'received_at' => '2026-05-04 03:25:00',
            'supplier_name' => 'Supplier Test',
            'lines' => [[
                'item_id' => $item->id,
                'qty' => 10,
                'unit_id' => $gram->id,
                'cost_mode' => 'UNIT',
                'unit_cost' => '195,00',
                'expired_at' => '2026-12-31',
            ]],
        ]);

        $response->assertRedirect(route('admin.receivings.index'));

        $purchase = Purchase::firstOrFail();
        $line = PurchaseLine::firstOrFail();
        $batch = ItemBatch::firstOrFail();
        $move = StockMove::firstOrFail();

        $this->assertSame('Supplier Test', $purchase->supplier_name);
        $this->assertSame(10.0, (float) $line->qty);
        $this->assertSame(195.0, (float) $line->unit_cost);
        $this->assertEqualsWithDelta(0.01, (float) $line->qty_base, 0.000001);
        $this->assertEqualsWithDelta(195000, (float) $line->unit_cost_base, 0.000001);
        $this->assertEqualsWithDelta(0.01, (float) $batch->qty_on_hand_base, 0.000001);
        $this->assertEqualsWithDelta(195000, (float) $batch->unit_cost_base, 0.000001);
        $this->assertEqualsWithDelta(0.01, (float) $move->qty_base, 0.000001);
    }

    public function test_total_cost_mode_is_normalized_before_base_conversion(): void
    {
        $this->withoutMiddleware();

        $user = $this->createUser();
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

        $this->actingAs($user)->post(route('admin.receivings.store'), [
            'received_at' => '2026-05-04 03:25:00',
            'lines' => [[
                'item_id' => $item->id,
                'qty' => 10,
                'unit_id' => $gram->id,
                'cost_mode' => 'TOTAL',
                'unit_cost' => '1.950',
                'expired_at' => '2026-12-31',
            ]],
        ])->assertRedirect(route('admin.receivings.index'));

        $line = PurchaseLine::firstOrFail();
        $batch = ItemBatch::firstOrFail();

        $this->assertSame(10.0, (float) $line->qty);
        $this->assertSame(195.0, (float) $line->unit_cost);
        $this->assertEqualsWithDelta(0.01, (float) $line->qty_base, 0.000001);
        $this->assertEqualsWithDelta(195000, (float) $batch->unit_cost_base, 0.000001);
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@example.test',
            'password' => Hash::make('password'),
        ]);
    }
}
