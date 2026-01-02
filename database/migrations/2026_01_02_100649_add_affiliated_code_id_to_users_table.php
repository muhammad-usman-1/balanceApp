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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('affiliated_code_id')->nullable()->after('goal');
            $table->foreign('affiliated_code_id', 'affiliated_code_fk')->references('id')->on('affiliated_codes')->onDelete('set null');
            $table->index('affiliated_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('affiliated_code_fk');
            $table->dropIndex(['affiliated_code_id']);
            $table->dropColumn('affiliated_code_id');
        });
    }
};
