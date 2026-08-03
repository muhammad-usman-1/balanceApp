<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meal_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('weekly_limit')->default(2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meal_groups');
    }
};
