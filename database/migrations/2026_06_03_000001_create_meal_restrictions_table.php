<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMealRestrictionsTable extends Migration
{
    public function up()
    {
        Schema::create('meal_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_id')->unique()->constrained('meals')->onDelete('cascade');
            $table->unsignedTinyInteger('weekly_limit')->default(2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meal_restrictions');
    }
}
