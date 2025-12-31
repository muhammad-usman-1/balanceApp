<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('area')->nullable();
            $table->string('block_number')->nullable();
            $table->string('street')->nullable();
            $table->string('house_building')->nullable();
            $table->string('floor_apartment')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('remarks')->nullable();
            $table->string('category')->default('home');
            $table->boolean('is_primary')->default(false);
            $table->string('preferred_delivery_slot')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};








