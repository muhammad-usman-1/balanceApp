<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCouponFieldsToUserSubcrptionsTable extends Migration
{
    public function up()
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->string('coupon_code', 50)->nullable()->after('payment_meta');
            $table->unsignedBigInteger('coupon_id')->nullable()->after('coupon_code');
            $table->decimal('discount_amount', 10, 3)->nullable()->after('coupon_id');

            $table->foreign('coupon_id')->references('id')->on('coupons')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_code', 'coupon_id', 'discount_amount']);
        });
    }
}
