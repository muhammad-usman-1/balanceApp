<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToSubscriptionMealsTable extends Migration
{
    public function up()
    {
        Schema::table('subscription_meals', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_plan_days_id')->nullable();
            $table->foreign('subscription_plan_days_id', 'subscription_plan_days_fk_10760917')->references('id')->on('subscription_plan_days');
            $table->unsignedBigInteger('meal_id')->nullable();
            $table->foreign('meal_id', 'meal_fk_10760918')->references('id')->on('meals');
            $table->unsignedBigInteger('type_id')->nullable();
            $table->foreign('type_id', 'type_fk_10760919')->references('id')->on('meals');
        });
    }
}
