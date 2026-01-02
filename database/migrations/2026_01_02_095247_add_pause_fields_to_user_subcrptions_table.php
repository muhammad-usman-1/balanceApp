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
            $table->boolean('is_paused')->default(false)->after('status');
            $table->datetime('paused_at')->nullable()->after('is_paused');
            $table->datetime('paused_until')->nullable()->after('paused_at');
            $table->integer('total_paused_days')->default(0)->after('paused_until'); // Total days paused across all pauses
            $table->date('original_end_date')->nullable()->after('total_paused_days'); // Store original end date before pause
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->dropColumn([
                'is_paused',
                'paused_at',
                'paused_until',
                'total_paused_days',
                'original_end_date'
            ]);
        });
    }
};
