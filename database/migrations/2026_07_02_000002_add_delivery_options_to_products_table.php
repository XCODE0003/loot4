<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            // Admin-editable list of delivery choices shown on checkout:
            // [{ "label": "Express (1–12h)", "price": 9.99 }, ...]. A price of 0
            // means free. Replaces the old express_delivery/fee/time fields.
            $table->json('delivery_options')->nullable()->after('express_time');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('delivery_options');
        });
    }
};
