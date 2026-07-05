<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('selling_price');
            $table->unsignedInteger('cost_price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('ingredients', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('unit', 30);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->unsignedInteger('current_stock')->default(0);
            $table->timestamps();
        });

        Schema::create('ingredient_stocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('type', 30);
            $table->integer('quantity');
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('recipes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->unique()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('recipe_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['recipe_id', 'ingredient_id']);
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone_number', 30)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->dateTime('transaction_date');
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('tax')->default(0);
            $table->unsignedInteger('grand_total')->default(0);
            $table->string('status', 30)->default('draft');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['transaction_date', 'status']);
        });

        Schema::create('transaction_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('menu_name');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price');
            $table->unsignedInteger('subtotal');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('method', 30);
            $table->unsignedInteger('amount_paid')->default(0);
            $table->unsignedInteger('change_amount')->default(0);
            $table->dateTime('paid_at')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('store_name');
            $table->text('address')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('logo_path')->nullable();
            $table->unsignedTinyInteger('tax_percentage')->default(0);
            $table->text('receipt_footer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('ingredient_stocks');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('categories');
    }
};
