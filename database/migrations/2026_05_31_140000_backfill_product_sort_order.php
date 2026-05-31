<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Give every product a unique, dense sort_order (1..N) preserving the current
     * order. Without this, all products share sort_order = 0, so drag-reordering
     * and the "Order" number have nothing to sort by.
     */
    public function up(): void
    {
        $ids = DB::table('products')->orderBy('sort_order')->orderBy('id')->pluck('id');

        foreach ($ids as $index => $id) {
            DB::table('products')->where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        // Order is not restorable; leave values in place.
    }
};
