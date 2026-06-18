<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_token', 64)->unique();           // UUID returned to app for polling
            $table->unsignedBigInteger('user_id');
            $table->json('subscription_data');                      // Full subscription payload stored before callback
            $table->decimal('amount', 10, 3);
            $table->string('currency', 3)->default('KWD');
            $table->string('payment_method', 20)->default('knet'); // knet | credit_card | cash
            $table->string('status', 20)->default('pending');      // pending | paid | failed
            $table->string('hesabe_payment_token')->nullable();     // Token received from Hesabe
            $table->string('hesabe_order_reference')->nullable();   // Reference we sent to Hesabe
            $table->json('hesabe_response')->nullable();            // Decrypted callback data from Hesabe
            $table->unsignedBigInteger('subscription_id')->nullable(); // Populated after subscription is created
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_orders');
    }
}
