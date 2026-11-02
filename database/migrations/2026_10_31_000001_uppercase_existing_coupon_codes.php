<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Coupon codes are now always stored uppercase — convert existing ones.
    public function up(): void
    {
        DB::table('coupons')->update(['coupon_code' => DB::raw('UPPER(TRIM(coupon_code))')]);
    }

    public function down(): void
    {
        // Original casing is not recoverable; nothing to undo.
    }
};
