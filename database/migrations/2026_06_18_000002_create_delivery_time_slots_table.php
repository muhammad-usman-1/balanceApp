<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDeliveryTimeSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_time_slots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('value')->unique();       // machine key sent in API/checkout
            $table->string('label_en');              // English display label
            $table->string('label_ar')->nullable();  // Arabic display label
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the two default slots
        DB::table('delivery_time_slots')->insert([
            [
                'value'      => 'four_pm_to_eight_pm',
                'label_en'   => '4:00 PM – 8:00 PM',
                'label_ar'   => '٤:٠٠ م – ٨:٠٠ م',
                'is_active'  => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value'      => 'eight_pm_to_midnight',
                'label_en'   => '8:00 PM – Midnight',
                'label_ar'   => '٨:٠٠ م – منتصف الليل',
                'is_active'  => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('delivery_time_slots');
    }
}
