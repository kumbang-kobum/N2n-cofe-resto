<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('DRAFT','OPEN','PAID','VOID','REFUND','CANCELLED') NOT NULL DEFAULT 'DRAFT'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('DRAFT','PAID','VOID','REFUND') NOT NULL DEFAULT 'DRAFT'");
    }
};
