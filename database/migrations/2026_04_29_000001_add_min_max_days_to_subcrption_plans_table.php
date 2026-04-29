<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinMaxDaysToSubcrptionPlansTable extends Migration
{
    public function up()
    {
        Schema::table('subcrption_plans', function (Blueprint $table) {
            $table->integer('min_days')->nullable()->after('snack_count');
            $table->integer('max_days')->nullable()->after('min_days');
        });
    }

    public function down()
    {
        Schema::table('subcrption_plans', function (Blueprint $table) {
            $table->dropColumn(['min_days', 'max_days']);
        });
    }
}
