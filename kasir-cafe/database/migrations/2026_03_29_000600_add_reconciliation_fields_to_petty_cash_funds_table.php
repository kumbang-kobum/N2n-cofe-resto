<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petty_cash_funds', function (Blueprint $table) {
            $table->decimal('counted_cash_amount', 18, 2)->nullable()->after('returned_amount');
            $table->decimal('difference_amount', 18, 2)->default(0)->after('counted_cash_amount');
            $table->text('reconciliation_note')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_funds', function (Blueprint $table) {
            $table->dropColumn(['counted_cash_amount', 'difference_amount', 'reconciliation_note']);
        });
    }
};
