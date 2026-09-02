<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('payments', 'qris_image')) {
                $table->string('qris_image')->nullable()->after('status');
            }

            if (! Schema::hasColumn('payments', 'qris_reference')) {
                $table->string('qris_reference')->nullable()->after('qris_image');
            }

            if (! Schema::hasColumn('payments', 'qris_amount')) {
                $table->unsignedInteger('qris_amount')->nullable()->after('qris_reference');
            }

            if (! Schema::hasColumn('payments', 'qris_status')) {
                $table->string('qris_status', 30)->default('pending')->after('qris_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn([
                'qris_image',
                'qris_reference',
                'qris_amount',
                'qris_status',
            ]);
        });
    }
};
