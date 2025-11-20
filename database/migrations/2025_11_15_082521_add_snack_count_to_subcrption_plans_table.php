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
        Schema::table('subcrption_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subcrption_plans', 'snack_count')) {
                $table->integer('snack_count')->nullable()->after('meal_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subcrption_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subcrption_plans', 'snack_count')) {
                $table->dropColumn('snack_count');
            }
        });
    }
};
