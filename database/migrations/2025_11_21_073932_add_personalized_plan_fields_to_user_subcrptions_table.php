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
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->boolean('is_personalized')->default(false)->after('status');
            $table->decimal('protein', 8, 2)->nullable()->after('is_personalized');
            $table->decimal('carbs', 8, 2)->nullable()->after('protein');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->dropColumn(['is_personalized', 'protein', 'carbs']);
        });
    }
};
