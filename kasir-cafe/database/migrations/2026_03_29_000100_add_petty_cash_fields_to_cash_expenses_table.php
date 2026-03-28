<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_expenses', function (Blueprint $table) {
            $table->enum('funding_source', ['DIRECT_CASH', 'PETTY_CASH'])->default('DIRECT_CASH')->after('amount');
            $table->foreignId('petty_cash_fund_id')->nullable()->after('cashier_id')->constrained('petty_cash_funds');
            $table->string('receipt_path')->nullable()->after('approval_note');

            $table->index('funding_source');
            $table->index('petty_cash_fund_id');
        });

        DB::table('cash_expenses')->whereNull('funding_source')->update([
            'funding_source' => 'DIRECT_CASH',
        ]);
    }

    public function down(): void
    {
        Schema::table('cash_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('petty_cash_fund_id');
            $table->dropColumn(['funding_source', 'receipt_path']);
        });
    }
};
