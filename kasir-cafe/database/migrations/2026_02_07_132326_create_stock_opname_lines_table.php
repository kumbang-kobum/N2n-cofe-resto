<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_opname_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->onDelete('cascade');

            $table->foreignId('item_id')->constrained('items');
            $table->decimal('system_qty_base', 18, 6)->default(0);
            $table->decimal('physical_qty_base', 18, 6)->default(0);
            $table->decimal('diff_qty_base', 18, 6)->default(0); // physical - system

            // input helper (audit)
            $table->decimal('physical_qty', 18, 6)->default(0);
            $table->foreignId('input_unit_id')->constrained('units');

            // kalau diff positif (tambah stok) harus ada expired & cost (opsional)
            $table->date('expired_at')->nullable();
            $table->decimal('unit_cost_base', 18, 6)->default(0);

            $table->timestamps();

            $table->index(['stock_opname_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_lines');
    }
};