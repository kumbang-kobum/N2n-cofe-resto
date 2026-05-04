<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Add REFUND to enum (MySQL)
        DB::statement("ALTER TABLE stock_moves MODIFY COLUMN type ENUM('RECEIPT','CONSUMPTION','EXPIRED_DISPOSAL','WASTE','ADJUSTMENT','REFUND') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE stock_moves MODIFY COLUMN type ENUM('RECEIPT','CONSUMPTION','EXPIRED_DISPOSAL','WASTE','ADJUSTMENT') NOT NULL");
    }
};
