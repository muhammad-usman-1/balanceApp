<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            // Set when the customer confirms payment for a queued renewal via any
            // of the 3 methods (cash/card/knet). Cash intentionally leaves
            // `payment` as "pending" (collected on delivery), so this is the
            // single reliable signal that renewal edits (days/address/plan/start
            // date) should now be locked — payment alone can't tell us that.
            $table->timestamp('renewal_confirmed_at')->nullable()->after('payment_gateway');
        });
    }

    public function down()
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->dropColumn('renewal_confirmed_at');
        });
    }
};
