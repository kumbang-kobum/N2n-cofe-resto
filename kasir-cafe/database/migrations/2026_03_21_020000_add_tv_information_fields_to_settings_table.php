<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('tv_video_path')->nullable()->after('logo_path');
            $table->text('tv_running_text')->nullable()->after('tv_video_path');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'tv_video_path',
                'tv_running_text',
            ]);
        });
    }
};
