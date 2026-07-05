<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('selling_price');
            $table->unsignedInteger('cost_price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['menu_id', 'name']);
        });

        Schema::table('transaction_items', function (Blueprint $table): void {
            $table->foreignId('menu_variant_id')->nullable()->after('menu_id')->constrained('menu_variants')->cascadeOnUpdate()->nullOnDelete();
            $table->string('variant_name')->nullable()->after('menu_name');
        });

        Menu::query()->each(function (Menu $menu): void {
            $variantId = DB::table('menu_variants')->insertGetId([
                'menu_id' => $menu->id,
                'name' => 'Regular',
                'selling_price' => $menu->selling_price,
                'cost_price' => $menu->cost_price,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('transaction_items')
                ->where('menu_id', $menu->id)
                ->whereNull('menu_variant_id')
                ->update([
                    'menu_variant_id' => $variantId,
                    'variant_name' => 'Regular',
                    'updated_at' => now(),
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('menu_variant_id');
            $table->dropColumn('variant_name');
        });

        Schema::dropIfExists('menu_variants');
    }
};
