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
            $table->unsignedInteger('profit_amount')->default(0)->after('cost_price');
        });

        DB::table('menu_variants')
            ->select(['id', 'selling_price', 'cost_price'])
            ->orderBy('id')
            ->each(function (object $variant): void {
                DB::table('menu_variants')
                    ->where('id', $variant->id)
                    ->update([
                        'profit_amount' => max(0, (int) $variant->selling_price - (int) $variant->cost_price),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('menu_variants', function (Blueprint $table): void {
            $table->dropColumn('profit_amount');
        });
    }
};
