<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_custom_options', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        collect([
            'Less Sugar',
            'No Sugar',
            'Normal Sugar',
            'Extra Ice',
            'Less Ice',
            'No Ice',
        ])->each(fn (string $name, int $index) => DB::table('item_custom_options')->insert([
            'name' => $name,
            'is_active' => true,
            'sort_order' => $index + 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('item_custom_options');
    }
};
