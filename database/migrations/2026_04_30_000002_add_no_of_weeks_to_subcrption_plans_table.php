<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoOfWeeksToSubcrptionPlansTable extends Migration
{
    public function up()
    {
        Schema::table('subcrption_plans', function (Blueprint $table) {
            $table->integer('no_of_weeks')->nullable()->after('max_days');
        });
    }

    public function down()
    {
        Schema::table('subcrption_plans', function (Blueprint $table) {
            $table->dropColumn('no_of_weeks');
        });
    }
}
