<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            // When true, `subscription_id` points at an EXISTING queued renewal that
            // should be updated (marked paid) on success — not a new subscription
            // to create. Lets the same Hesabe checkout/callback flow serve both
            // first-time checkout and paying for a queued renewal.
            $table->boolean('is_renewal')->default(false)->after('subscription_id');
        });
    }

    public function down()
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->dropColumn('is_renewal');
        });
    }
};
