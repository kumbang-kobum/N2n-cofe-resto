<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('type', ['DAMAGE', 'DISPOSAL']);
            $table->date('incident_date');
            $table->text('description')->nullable();
            $table->decimal('cost', 18, 2)->default(0);
            $table->enum('status', ['OPEN', 'RESOLVED'])->default('OPEN');
            $table->foreignId('reported_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_incidents');
    }
};
