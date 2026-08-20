<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subcrption_plans', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('title');
            $table->text('description_ar')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('subcrption_plans', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'description_ar']);
        });
    }
};
