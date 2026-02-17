<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->date('period_month');
            $table->foreignId('employee_id')->constrained('users');
            $table->decimal('base_salary', 18, 2)->default(0);
            $table->decimal('overtime_amount', 18, 2)->default(0);
            $table->decimal('bonus_amount', 18, 2)->default(0);
            $table->decimal('deduction_amount', 18, 2)->default(0);
            $table->decimal('net_amount', 18, 2)->default(0);
            $table->enum('status', ['DRAFT', 'APPROVED', 'PAID'])->default('DRAFT');
            $table->text('note')->nullable();
            $table->text('approval_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users');
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('period_month');
            $table->index('status');
            $table->unique(['period_month', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
