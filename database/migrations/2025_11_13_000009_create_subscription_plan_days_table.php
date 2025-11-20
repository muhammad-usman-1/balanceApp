<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlanDaysTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_plan_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('day')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
