<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('menu_type', 10)->nullable()->after('price_default');
        });

        DB::table('products')
            ->whereNull('menu_type')
            ->update(['menu_type' => Product::TYPE_FOOD]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('menu_type');
        });
    }
};
