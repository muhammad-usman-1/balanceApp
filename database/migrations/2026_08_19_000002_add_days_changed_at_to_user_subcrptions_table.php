<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            // When the customer used their one-time delivery-day change for this
            // subscription cycle. NULL = not used yet. Resets naturally on renewal
            // because a renewal is a brand-new user_subcrptions row.
            $table->timestamp('days_changed_at')->nullable()->after('selected_days');
        });
    }

    public function down()
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->dropColumn('days_changed_at');
        });
    }
};
