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
        // First, drop foreign key constraint from subscription_meals table
        Schema::table('subscription_meals', function (Blueprint $table) {
            $table->dropForeign('subscription_plan_days_fk_10760917');
        });

        // Drop the subscription_plan_days_id column from subscription_meals table
        Schema::table('subscription_meals', function (Blueprint $table) {
            $table->dropColumn('subscription_plan_days_id');
        });

        // Now drop the subscription_plan_days table
        Schema::dropIfExists('subscription_plan_days');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the subscription_plan_days table
        Schema::create('subscription_plan_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('day')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add relationship field back to subscription_plan_days table
        Schema::table('subscription_plan_days', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_plans_id')->nullable();
            $table->foreign('subscription_plans_id', 'subscription_plans_fk_10760911')->references('id')->on('subcrption_plans');
        });

        // Add subscription_plan_days_id column back to subscription_meals table
        Schema::table('subscription_meals', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_plan_days_id')->nullable();
            $table->foreign('subscription_plan_days_id', 'subscription_plan_days_fk_10760917')->references('id')->on('subscription_plan_days');
        });
    }
};
