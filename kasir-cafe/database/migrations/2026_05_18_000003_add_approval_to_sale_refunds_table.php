<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_refunds', function (Blueprint $table) {
            $table->string('status', 20)->default('PENDING')->after('note');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('rejection_note')->nullable()->after('approved_at');
        });

        // Refund lama yang sudah diproses → APPROVED
        DB::table('sale_refunds')->update([
            'status'      => 'APPROVED',
            'approved_by' => DB::raw('refunded_by'),
            'approved_at' => DB::raw('refunded_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('sale_refunds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['status', 'approved_at', 'rejection_note']);
        });
    }
};
