<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('meal_meal_extra', function (Blueprint $table) {
            // Per-meal cap on how many options the customer may pick from this extra
            // (e.g. Meal A: 2 sauces, Meal B: 1 sauce). NULL = no cap.
            $table->unsignedTinyInteger('max_select')->nullable()->after('is_required');
        });
    }

    public function down()
    {
        Schema::table('meal_meal_extra', function (Blueprint $table) {
            $table->dropColumn('max_select');
        });
    }
};
