<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Which extra categories a meal offers (e.g. this burger has Bread + Sauce).
        Schema::create('meal_meal_extra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_id')->constrained('meals')->cascadeOnDelete();
            $table->foreignId('meal_extra_id')->constrained('meal_extras')->cascadeOnDelete();
            // Optional per-meal override of the extra's default required flag.
            $table->boolean('is_required')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['meal_id', 'meal_extra_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('meal_meal_extra');
    }
};
