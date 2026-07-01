<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            // Billing details captured at checkout (country already exists).
            $table->string('first_name')->nullable()->after('email');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('last_name');
            $table->string('state')->nullable()->after('country');
            $table->string('town')->nullable()->after('state');
            $table->string('address')->nullable()->after('town');
            $table->string('postal_code')->nullable()->after('address');

            // Delivery-speed choice and its (server-computed) fee, added to total.
            $table->string('delivery_method')->nullable()->default('standard')->after('postal_code');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('delivery_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'state',
                'town',
                'address',
                'postal_code',
                'delivery_method',
                'delivery_fee',
            ]);
        });
    }
};
