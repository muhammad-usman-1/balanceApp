<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_subcrption_id');
            $table->unsignedBigInteger('subscription_day_id');
            $table->date('delivery_date');
            $table->enum('status', ['pending', 'delivered'])->default('pending');
            $table->timestamps();

            $table->unique(['user_subcrption_id', 'subscription_day_id', 'delivery_date'], 'delivery_orders_unique');
            $table->foreign('user_subcrption_id')->references('id')->on('user_subcrptions')->onDelete('cascade');
            $table->foreign('subscription_day_id')->references('id')->on('subscription_days')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_orders');
    }
}
