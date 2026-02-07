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
        Schema::create('sales', function (Blueprint $table) {
    $table->id();
    $table->enum('status', ['DRAFT','PAID','VOID','REFUND'])->default('DRAFT');
    $table->dateTime('paid_at')->nullable();
    $table->decimal('total', 18, 2)->default(0);
    $table->decimal('cogs_total', 18, 2)->default(0);
    $table->decimal('profit_gross', 18, 2)->default(0);
    $table->string('payment_method')->nullable();
    $table->foreignId('cashier_id')->constrained('users');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
