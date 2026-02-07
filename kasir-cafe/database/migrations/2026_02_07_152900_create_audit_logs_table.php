<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('action', 80)->index();
            $table->string('auditable_type', 120)->index();
            $table->unsignedBigInteger('auditable_id')->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['auditable_type','auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};