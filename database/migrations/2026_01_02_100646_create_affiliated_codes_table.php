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
        Schema::create('affiliated_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('full_name');
            $table->string('code')->unique();
            $table->enum('gift_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('gift_value', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliated_codes');
    }
};
