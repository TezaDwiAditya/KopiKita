<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_variants', function (Blueprint $table): void {
            $table->unsignedSmallInteger('recipe_multiplier')->default(1)->after('cost_price');
        });

        DB::table('menu_variants')->where('name', '250 ml')->update(['recipe_multiplier' => 1]);
        DB::table('menu_variants')->where('name', '500 ml')->update(['recipe_multiplier' => 2]);
        DB::table('menu_variants')->where('name', '1 Liter')->update(['recipe_multiplier' => 4]);
    }

    public function down(): void
    {
        Schema::table('menu_variants', function (Blueprint $table): void {
            $table->dropColumn('recipe_multiplier');
        });
    }
};
