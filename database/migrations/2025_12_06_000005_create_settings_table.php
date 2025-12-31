<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('delivery_time_slot_1_en')->nullable();
            $table->string('delivery_time_slot_2_en')->nullable();
            $table->boolean('payment_knet')->default(true);
            $table->boolean('payment_credit_card')->default(true);
            $table->boolean('payment_cash')->default(true);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            'delivery_time_slot_1_en' => '4pm to 8pm',
            'delivery_time_slot_2_en' => '8pm to 12am',
            'payment_knet' => true,
            'payment_credit_card' => true,
            'payment_cash' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}

