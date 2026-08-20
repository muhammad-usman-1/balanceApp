<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meal_extra_ingredients', function (Blueprint $table) {
            $table->id();
            // Each ingredient belongs to exactly one extra category (Bread -> Brown Bread).
            $table->foreignId('meal_extra_id')->constrained('meal_extras')->cascadeOnDelete();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meal_extra_ingredients');
    }
};
