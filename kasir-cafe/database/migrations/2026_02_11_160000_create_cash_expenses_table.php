<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_expenses', function (Blueprint $table) {
            $table->id();
            $table->dateTime('expense_at');
            $table->string('category', 100);
            $table->decimal('amount', 18, 2);
            $table->text('note')->nullable();
            $table->foreignId('cashier_id')->constrained('users');
            $table->timestamps();

            $table->index('expense_at');
            $table->index('cashier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_expenses');
    }
};
