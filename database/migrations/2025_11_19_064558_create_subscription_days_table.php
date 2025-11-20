<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_subcrptions_id')->nullable();
            $table->foreign('user_subcrptions_id', 'user_subcrptions_fk_subscription_days')->references('id')->on('user_subcrptions');
            $table->string('day')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_days');
    }
};
