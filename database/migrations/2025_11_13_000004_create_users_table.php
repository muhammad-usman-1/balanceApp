<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->datetime('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('remember_token')->nullable();
            $table->integer('otp')->nullable();
            $table->integer('mobile')->nullable();
            $table->string('gender')->nullable();
            $table->float('height', 15, 2)->nullable();
            $table->float('weight', 15, 2)->nullable();
            $table->date('dob')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
