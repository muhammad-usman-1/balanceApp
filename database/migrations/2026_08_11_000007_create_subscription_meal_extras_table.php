<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // The customer's actual extra choices for a meal in their subscription
        // (e.g. their Monday burger = Whole Wheat + Ketchup). The parent extra is
        // derived from the ingredient — not stored here.
        Schema::create('subscription_meal_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_meal_id')->constrained('subscription_meals')->cascadeOnDelete();
            $table->foreignId('meal_extra_ingredient_id')->constrained('meal_extra_ingredients')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['subscription_meal_id', 'meal_extra_ingredient_id'], 'sub_meal_extra_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_meal_extras');
    }
};
