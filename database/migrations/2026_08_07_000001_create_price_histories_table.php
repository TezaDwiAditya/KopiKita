<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table): void {
            $table->id();
            $table->morphs('priceable');
            $table->string('item_type');
            $table->string('item_name');
            $table->string('price_type');
            $table->unsignedInteger('old_price')->default(0);
            $table->unsignedInteger('new_price')->default(0);
            $table->integer('difference')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('changed_by')->nullable();
            $table->timestamps();

            $table->index(['item_type', 'price_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
