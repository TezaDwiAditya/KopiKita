<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->foreignId('customer_group_id')
                ->nullable()
                ->after('phone_number')
                ->constrained('customer_groups')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::create('menu_variant_group_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_variant_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('selling_price');
            $table->timestamps();

            $table->unique(['menu_variant_id', 'customer_group_id'], 'variant_group_price_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_variant_group_prices');

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('customer_group_id');
        });

        Schema::dropIfExists('customer_groups');
    }
};
