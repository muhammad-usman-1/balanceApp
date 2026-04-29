<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchAreaToUserSubcrptionsTable extends Migration
{
    public function up()
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('user_address_id');
            $table->unsignedBigInteger('area_id')->nullable()->after('branch_id');

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('user_subcrptions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['area_id']);
            $table->dropColumn(['branch_id', 'area_id']);
        });
    }
}
