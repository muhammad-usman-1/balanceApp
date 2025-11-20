<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDurationsTable extends Migration
{
    public function up()
    {
        Schema::create('durations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->integer('no_of_weeks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
