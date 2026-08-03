<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * price was created as an integer column, which silently rounds off
     * decimal values (e.g. 89.4 -> 89) on every insert/update. Subscription
     * prices are computed with 3-decimal precision (round(..., 3)) in
     * SubscriptionService, so the column must support that precision.
     *
     * Raw SQL is used instead of Blueprint::change() to avoid requiring
     * doctrine/dbal, which isn't installed in this project.
     */
    public function up()
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite has no real column types; existing data is preserved as-is.
            return;
        }

        DB::statement('ALTER TABLE user_subcrptions MODIFY price DECIMAL(10,3) NULL');
    }

    public function down()
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE user_subcrptions MODIFY price INT NULL');
    }
};
