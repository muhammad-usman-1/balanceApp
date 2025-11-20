<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToUserSubcrptionsTable extends Migration
{
    public function up()
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_10760903')->references('id')->on('users');
            $table->unsignedBigInteger('subcrption_plans_id')->nullable();
            $table->foreign('subcrption_plans_id', 'subcrption_plans_fk_10760906')->references('id')->on('subcrption_plans');
            $table->unsignedBigInteger('duration_id')->nullable();
            $table->foreign('duration_id', 'duration_fk_10760907')->references('id')->on('durations');
        });
    }
}
