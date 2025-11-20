<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubcrptionsTable extends Migration
{
    public function up()
    {
        Schema::create('user_subcrptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('selected_days')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('price')->nullable();
            $table->string('payment')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
