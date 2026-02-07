<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('item_batches', 'item_id')) {
                $table->foreignId('item_id')->constrained('items');
            }

            if (!Schema::hasColumn('item_batches', 'received_at')) {
                $table->dateTime('received_at')->nullable()->index();
            }

            if (!Schema::hasColumn('item_batches', 'expired_at')) {
                $table->date('expired_at')->index();
            }

            if (!Schema::hasColumn('item_batches', 'qty_on_hand_base')) {
                $table->decimal('qty_on_hand_base', 18, 6)->default(0);
            }

            if (!Schema::hasColumn('item_batches', 'unit_cost_base')) {
                $table->decimal('unit_cost_base', 18, 6)->default(0);
            }

            // index untuk FEFO cepat
            $table->index(['item_id', 'expired_at', 'status'], 'idx_batches_fefo');
        });
    }

    public function down(): void
    {
        Schema::table('item_batches', function (Blueprint $table) {
            // Hati-hati: drop kolom bisa merusak data, tapi ini untuk rollback dev
            if (Schema::hasColumn('item_batches', 'qty_on_hand_base')) $table->dropColumn('qty_on_hand_base');
            if (Schema::hasColumn('item_batches', 'unit_cost_base')) $table->dropColumn('unit_cost_base');
            if (Schema::hasColumn('item_batches', 'expired_at')) $table->dropColumn('expired_at');
            if (Schema::hasColumn('item_batches', 'received_at')) $table->dropColumn('received_at');

            if (Schema::hasColumn('item_batches', 'item_id')) {
                $table->dropForeign(['item_id']);
                $table->dropColumn('item_id');
            }

            // drop index jika ada
            $table->dropIndex('idx_batches_fefo');
        });
    }
};