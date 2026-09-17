<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('name_ar');
        });

        // Backfill: preserve current (id-based) order as the initial sort order.
        DB::table('categories')->orderBy('id')->pluck('id')->each(function ($id, $index) {
            DB::table('categories')->where('id', $id)->update(['sort_order' => $index]);
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
