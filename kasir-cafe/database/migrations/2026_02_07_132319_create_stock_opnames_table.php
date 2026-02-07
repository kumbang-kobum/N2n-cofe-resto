<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SOP-20260207-0001
            $table->date('counted_at');
            $table->string('status')->default('DRAFT'); // DRAFT, POSTED, CANCELLED
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->dateTime('posted_at')->nullable();

            $table->timestamps();
            $table->index(['counted_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};