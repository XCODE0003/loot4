<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_form_fields', function (Blueprint $table): void {
            // 'addon' = option price adds to the total; 'absolute' = the selected
            // option's price becomes the product price (variant / "choose amount").
            $table->string('pricing_mode')->default('addon')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('product_form_fields', function (Blueprint $table): void {
            $table->dropColumn('pricing_mode');
        });
    }
};
