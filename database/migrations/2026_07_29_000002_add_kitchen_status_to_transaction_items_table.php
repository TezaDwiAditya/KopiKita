<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table): void {
            $table->string('kitchen_status', 30)->default('pending')->after('note');
            $table->dateTime('preparing_at')->nullable()->after('kitchen_status');
            $table->dateTime('ready_at')->nullable()->after('preparing_at');
            $table->dateTime('served_at')->nullable()->after('ready_at');

            $table->index('kitchen_status');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table): void {
            $table->dropIndex(['kitchen_status']);
            $table->dropColumn([
                'kitchen_status',
                'preparing_at',
                'ready_at',
                'served_at',
            ]);
        });
    }
};
