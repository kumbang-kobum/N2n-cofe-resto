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
        Schema::create('recipe_lines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
    $table->foreignId('item_id')->constrained('items');
    $table->decimal('qty', 18, 6);
    $table->foreignId('unit_id')->constrained('units');
    $table->timestamps();
    $table->unique(['recipe_id','item_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_lines');
    }
};
