<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_lines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
    $table->foreignId('item_id')->constrained('items');
    $table->decimal('qty', 18, 6);
    $table->foreignId('unit_id')->constrained('units');
    $table->decimal('unit_cost', 18, 6); // cost per unit input (mis. per kg)
    $table->date('expired_at');          // wajib
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_lines');
    }
};
