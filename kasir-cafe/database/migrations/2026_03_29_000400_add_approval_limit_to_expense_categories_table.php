<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->decimal('approval_limit_amount', 18, 2)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn('approval_limit_amount');
        });
    }
};
