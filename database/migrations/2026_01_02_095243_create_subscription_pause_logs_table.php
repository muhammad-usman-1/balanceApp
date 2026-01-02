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
        Schema::create('subscription_pause_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_subcrption_id');
            $table->foreign('user_subcrption_id', 'user_subcrption_fk_pause_log')->references('id')->on('user_subcrptions')->onDelete('cascade');
            $table->enum('action', ['pause', 'resume']); // pause or resume
            $table->datetime('action_timestamp'); // When the action was performed
            $table->datetime('paused_at')->nullable(); // When subscription was paused
            $table->datetime('resumed_at')->nullable(); // When subscription was resumed
            $table->integer('paused_days')->nullable(); // Number of days paused
            $table->text('reason')->nullable(); // Reason for pausing
            $table->enum('performed_by_type', ['admin', 'user']); // Who performed the action
            $table->unsignedBigInteger('performed_by_id')->nullable(); // ID of admin or user who performed the action
            $table->string('performed_by_name')->nullable(); // Name of the person who performed the action
            $table->text('notes')->nullable(); // Additional notes
            $table->json('metadata')->nullable(); // Additional metadata (original dates, etc.)
            $table->timestamps();
            
            $table->index('user_subcrption_id');
            $table->index('action');
            $table->index('action_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_pause_logs');
    }
};
