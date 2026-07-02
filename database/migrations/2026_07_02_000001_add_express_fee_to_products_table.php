<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            // Per-product Express delivery price and time. When null, checkout
            // falls back to the global Settings → Delivery values, so products
            // created before this change keep working unchanged.
            $table->decimal('express_fee', 8, 2)->nullable()->after('express_delivery');
            $table->string('express_time')->nullable()->after('express_fee');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['express_fee', 'express_time']);
        });
    }
};
