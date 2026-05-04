<?php

namespace Tests\Feature\Admin;

use App\Models\Item;
use App\Models\StockOpnameLine;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StockOpnameControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_opname_converts_physical_qty_and_cost_to_base_unit(): void
    {
        $this->withoutMiddleware();

        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'stock-opname@example.test',
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

        $this->actingAs($user)->post(route('admin.stock_opname.store'), [
            'counted_at' => '2026-05-04',
            'lines' => [[
                'include' => '1',
                'item_id' => $item->id,
                'unit_id' => $gram->id,
                'physical_qty' => '10',
                'unit_cost' => '195,00',
                'expired_at' => '2026-12-31',
            ]],
        ])->assertRedirect();

        $line = StockOpnameLine::firstOrFail();

        $this->assertSame(10.0, (float) $line->physical_qty);
        $this->assertEqualsWithDelta(0.01, (float) $line->physical_qty_base, 0.000001);
        $this->assertEqualsWithDelta(0.01, (float) $line->diff_qty_base, 0.000001);
        $this->assertEqualsWithDelta(195000, (float) $line->unit_cost_base, 0.000001);
    }
}
