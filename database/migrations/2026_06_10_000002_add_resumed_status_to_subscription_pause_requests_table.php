<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddResumedStatusToSubscriptionPauseRequestsTable extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE subscription_pause_requests MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled','resumed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE subscription_pause_requests MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending'");
    }
}
