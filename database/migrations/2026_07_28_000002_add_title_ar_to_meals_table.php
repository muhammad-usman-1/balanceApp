<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('title');
        });
    }

    public function down()
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->dropColumn('title_ar');
        });
    }
};
