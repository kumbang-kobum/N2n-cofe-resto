<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index('paid_at', 'sales_paid_at_idx');
        });

        Schema::table('stock_moves', function (Blueprint $table) {
            $table->index('item_id', 'stock_moves_item_id_idx');
        });

        Schema::table('recipe_lines', function (Blueprint $table) {
            $table->index('item_id', 'recipe_lines_item_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_paid_at_idx');
        });

        Schema::table('stock_moves', function (Blueprint $table) {
            $table->dropIndex('stock_moves_item_id_idx');
        });

        Schema::table('recipe_lines', function (Blueprint $table) {
            $table->dropIndex('recipe_lines_item_id_idx');
        });
    }
};
