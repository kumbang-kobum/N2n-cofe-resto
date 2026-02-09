<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('license_key')->nullable()->after('logo_path');
            $table->date('license_expires_at')->nullable()->after('license_key');
            $table->timestamp('installed_at')->nullable()->after('license_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['license_key', 'license_expires_at', 'installed_at']);
        });
    }
};
