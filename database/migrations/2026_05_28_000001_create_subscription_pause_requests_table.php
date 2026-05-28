<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPauseRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_pause_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_subcrption_id')->constrained('user_subcrptions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('pause_start_date');
            $table->date('pause_end_date');
            $table->unsignedInteger('pause_days');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_pause_requests');
    }
}
