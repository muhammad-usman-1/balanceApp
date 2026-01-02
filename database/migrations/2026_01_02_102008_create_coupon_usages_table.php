<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coupon_id');
            $table->foreign('coupon_id', 'coupon_fk_usage')->references('id')->on('coupons')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'user_fk_coupon_usage')->references('id')->on('users')->onDelete('cascade');
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('order_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['coupon_id', 'user_id']);
            $table->index('user_id');
            $table->index('coupon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
