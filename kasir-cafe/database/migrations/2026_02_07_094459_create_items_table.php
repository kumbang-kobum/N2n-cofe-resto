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
        Schema::create('items', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('base_unit_id')->constrained('units'); // g/ml/pcs
    $table->boolean('track_expiry')->default(true); // wajib true
    $table->decimal('min_stock', 18, 6)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
