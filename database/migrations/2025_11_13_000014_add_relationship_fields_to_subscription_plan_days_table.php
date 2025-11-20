<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToSubscriptionPlanDaysTable extends Migration
{
    public function up()
    {
        Schema::table('subscription_plan_days', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_plans_id')->nullable();
            $table->foreign('subscription_plans_id', 'subscription_plans_fk_10760911')->references('id')->on('subcrption_plans');
        });
    }
}
