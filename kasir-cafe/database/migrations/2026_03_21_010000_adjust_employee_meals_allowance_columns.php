<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees') && ! Schema::hasColumn('employees', 'meal_allowance_monthly')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->decimal('meal_allowance_monthly', 18, 2)
                    ->nullable()
                    ->after('department');
            });

            if (Schema::hasColumn('employees', 'meal_quota_monthly')) {
                DB::statement('UPDATE employees SET meal_allowance_monthly = meal_quota_monthly WHERE meal_allowance_monthly IS NULL');
            }
        }

        if (Schema::hasTable('employee_meals') && ! Schema::hasColumn('employee_meals', 'is_over_allowance')) {
            Schema::table('employee_meals', function (Blueprint $table) {
                $table->boolean('is_over_allowance')
                    ->default(false)
                    ->after('excess_amount');
            });

            if (Schema::hasColumn('employee_meals', 'is_over_quota')) {
                DB::statement('UPDATE employee_meals SET is_over_allowance = is_over_quota');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_meals') && Schema::hasColumn('employee_meals', 'is_over_allowance')) {
            Schema::table('employee_meals', function (Blueprint $table) {
                $table->dropColumn('is_over_allowance');
            });
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'meal_allowance_monthly')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('meal_allowance_monthly');
            });
        }
    }
};
