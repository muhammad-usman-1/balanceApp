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
        Schema::table('subscription_meals', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_days_id')->nullable()->after('subscription_plan_days_id');
            $table->foreign('subscription_days_id', 'subscription_days_fk_subscription_meals')->references('id')->on('subscription_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_meals', function (Blueprint $table) {
            $table->dropForeign('subscription_days_fk_subscription_meals');
            $table->dropColumn('subscription_days_id');
        });
    }
};
