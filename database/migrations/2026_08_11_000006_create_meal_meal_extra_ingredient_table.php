<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Which specific ingredient options are enabled for a meal (the per-meal
        // subset). The extra is derived from the ingredient — never stored twice.
        Schema::create('meal_meal_extra_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_id')->constrained('meals')->cascadeOnDelete();
            $table->foreignId('meal_extra_ingredient_id')->constrained('meal_extra_ingredients')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['meal_id', 'meal_extra_ingredient_id'], 'meal_extra_ingredient_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('meal_meal_extra_ingredient');
    }
};
