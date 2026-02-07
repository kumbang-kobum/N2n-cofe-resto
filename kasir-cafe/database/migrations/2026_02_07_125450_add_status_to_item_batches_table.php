<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_batches', function (Blueprint $table) {
            // kalau sudah ada kolom status, jangan tambah lagi
            if (!Schema::hasColumn('item_batches', 'status')) {
                $table->enum('status', ['ACTIVE', 'DEPLETED', 'EXPIRED'])
                    ->default('ACTIVE');
            }

            // index aman: hanya tambah kalau belum ada
            // (Laravel tidak punya hasIndex built-in yang portable, jadi kita skip dulu index jika mau aman)
        });
    }

    public function down(): void
    {
        Schema::table('item_batches', function (Blueprint $table) {
            if (Schema::hasColumn('item_batches', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};