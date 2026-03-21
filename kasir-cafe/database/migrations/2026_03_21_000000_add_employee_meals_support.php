<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('meal_allowance_monthly', 18, 2)
                ->nullable()
                ->after('department');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('meal_deduction_amount', 18, 2)
                ->default(0)
                ->after('deduction_amount');
        });

        Schema::create('employee_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('cashier_id')->nullable()->constrained('users');
            $table->dateTime('consumed_at');
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->decimal('cogs_total', 18, 2)->default(0);
            $table->decimal('expense_cogs_total', 18, 2)->default(0);
            $table->decimal('company_covered_amount', 18, 2)->default(0);
            $table->decimal('excess_amount', 18, 2)->default(0);
            $table->boolean('is_over_allowance')->default(false);
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['employee_id', 'consumed_at']);
            $table->index(['cashier_id', 'consumed_at']);
            $table->index(['payroll_id']);
            $table->index(['is_over_allowance', 'consumed_at']);
        });

        Schema::create('employee_meal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_meal_id')->constrained('employee_meals')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('qty', 18, 2)->default(1);
            $table->decimal('price', 18, 2)->default(0);
            $table->decimal('line_total', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_meal_lines');
        Schema::dropIfExists('employee_meals');

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('meal_deduction_amount');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('meal_allowance_monthly');
        });
    }
};
