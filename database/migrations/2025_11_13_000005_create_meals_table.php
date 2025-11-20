<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMealsTable extends Migration
{
    public function up()
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('calories')->nullable();
            $table->float('protein_g', 15, 2)->nullable();
            $table->float('fat_g', 15, 2)->nullable();
            $table->float('carbs_g', 15, 2)->nullable();
            $table->string('extras')->nullable();
            $table->integer('is_active')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }
}
