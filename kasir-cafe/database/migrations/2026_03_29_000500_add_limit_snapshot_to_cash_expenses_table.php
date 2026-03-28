<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_expenses', function (Blueprint $table) {
            $table->decimal('approval_limit_amount_snapshot', 18, 2)->nullable()->after('expense_category_id');
            $table->boolean('exceeds_approval_limit')->default(false)->after('approval_limit_amount_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('cash_expenses', function (Blueprint $table) {
            $table->dropColumn(['approval_limit_amount_snapshot', 'exceeds_approval_limit']);
        });
    }
};
