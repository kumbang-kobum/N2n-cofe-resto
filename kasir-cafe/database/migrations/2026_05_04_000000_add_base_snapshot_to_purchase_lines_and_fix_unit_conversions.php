<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_lines', 'qty_base')) {
                $table->decimal('qty_base', 18, 6)->nullable();
            }

            if (! Schema::hasColumn('purchase_lines', 'unit_cost_base')) {
                $table->decimal('unit_cost_base', 18, 6)->nullable();
            }
        });

        $units = DB::table('units')
            ->whereIn('symbol', ['g', 'kg', 'ml', 'l'])
            ->pluck('id', 'symbol');

        $this->updateConversion($units['kg'] ?? null, $units['g'] ?? null, 1000);
        $this->updateConversion($units['g'] ?? null, $units['kg'] ?? null, 0.001);
        $this->updateConversion($units['l'] ?? null, $units['ml'] ?? null, 1000);
        $this->updateConversion($units['ml'] ?? null, $units['l'] ?? null, 0.001);
    }

    public function down(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_lines', 'unit_cost_base')) {
                $table->dropColumn('unit_cost_base');
            }

            if (Schema::hasColumn('purchase_lines', 'qty_base')) {
                $table->dropColumn('qty_base');
            }
        });
    }

    private function updateConversion(?int $fromUnitId, ?int $toUnitId, float $multiplier): void
    {
        if (! $fromUnitId || ! $toUnitId) {
            return;
        }

        DB::table('unit_conversions')->updateOrInsert(
            ['from_unit_id' => $fromUnitId, 'to_unit_id' => $toUnitId],
            [
                'multiplier' => $multiplier,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
};
