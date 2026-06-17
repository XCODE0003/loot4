<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_form_fields', function (Blueprint $table): void {
            // How many radio/checkbox options sit per row on the product page (1 or 2).
            $table->unsignedTinyInteger('options_columns')->default(1)->after('options');
        });
    }

    public function down(): void
    {
        Schema::table('product_form_fields', function (Blueprint $table): void {
            $table->dropColumn('options_columns');
        });
    }
};
