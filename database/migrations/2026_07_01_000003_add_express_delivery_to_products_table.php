<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            // Whether this product offers the paid Express delivery option at
            // checkout. Express only appears when every item in the cart offers it.
            $table->boolean('express_delivery')->default(false)->after('auto_delivery');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('express_delivery');
        });
    }
};
