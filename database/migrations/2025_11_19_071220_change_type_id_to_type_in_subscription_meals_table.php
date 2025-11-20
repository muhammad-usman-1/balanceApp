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
        Schema::table('subscription_meals', function (Blueprint $table) {
            // Drop foreign key constraint by name if it exists
            try {
                $table->dropForeign('type_fk_10760919');
            } catch (\Exception $e) {
                // Foreign key might not exist, try dropping by column
                try {
                    $table->dropForeign(['type_id']);
                } catch (\Exception $e) {
                    // Ignore if foreign key doesn't exist
                }
            }
        });
        
        Schema::table('subscription_meals', function (Blueprint $table) {
            // Drop the type_id column if it exists
            if (Schema::hasColumn('subscription_meals', 'type_id')) {
                $table->dropColumn('type_id');
            }
        });
        
        Schema::table('subscription_meals', function (Blueprint $table) {
            // Add type column as string
            if (!Schema::hasColumn('subscription_meals', 'type')) {
                $table->string('type')->nullable()->after('meal_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_meals', function (Blueprint $table) {
            // Drop type column
            $table->dropColumn('type');
            // Add back type_id column
            $table->unsignedBigInteger('type_id')->nullable()->after('meal_id');
            $table->foreign('type_id', 'type_fk_10760919')->references('id')->on('meals');
        });
    }
};
