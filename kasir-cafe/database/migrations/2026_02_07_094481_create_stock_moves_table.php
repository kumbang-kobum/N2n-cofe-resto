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
        Schema::create('stock_moves', function (Blueprint $table) {
    $table->id();
    $table->dateTime('moved_at');
    $table->foreignId('item_id')->constrained('items');
    $table->foreignId('batch_id')->constrained('item_batches');
    $table->decimal('qty_base', 18, 6); // + masuk, - keluar
    $table->enum('type', ['RECEIPT','CONSUMPTION','EXPIRED_DISPOSAL','WASTE','ADJUSTMENT']);
    $table->string('ref_type')->nullable();
    $table->unsignedBigInteger('ref_id')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->string('note')->nullable();
    $table->timestamps();
    $table->index(['item_id','moved_at']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_moves');
    }
};
