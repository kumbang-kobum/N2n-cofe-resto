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
        Schema::create('unit_conversions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('from_unit_id')->constrained('units');
    $table->foreignId('to_unit_id')->constrained('units');
    $table->decimal('multiplier', 18, 8); // qty_to = qty_from * multiplier
    $table->timestamps();
    $table->unique(['from_unit_id','to_unit_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
