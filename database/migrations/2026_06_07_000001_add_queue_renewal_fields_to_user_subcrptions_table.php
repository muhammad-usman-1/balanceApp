<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->unsignedBigInteger('queued_after_subscription_id')->nullable()->after('status');
            $table->foreign('queued_after_subscription_id')
                  ->references('id')->on('user_subcrptions')->onDelete('set null');
            $table->boolean('auto_renew')->default(true)->after('queued_after_subscription_id');
            $table->timestamp('renewal_notified_at')->nullable()->after('auto_renew');
        });
    }

    public function down(): void
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->dropForeign(['queued_after_subscription_id']);
            $table->dropColumn(['queued_after_subscription_id', 'auto_renew', 'renewal_notified_at']);
        });
    }
};
