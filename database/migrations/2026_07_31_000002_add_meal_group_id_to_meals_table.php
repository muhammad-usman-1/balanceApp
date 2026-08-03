<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->foreignId('meal_group_id')->nullable()->after('category_id')
                ->constrained('meal_groups')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->dropForeign(['meal_group_id']);
            $table->dropColumn('meal_group_id');
        });
    }
};
