<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToMealsTable extends Migration
{
    public function up()
    {
        $hasForeignKey = $this->hasCategoryForeignKey();

        Schema::table('meals', function (Blueprint $table) use ($hasForeignKey) {
            if (!Schema::hasColumn('meals', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('description');
            }

            if (! $hasForeignKey) {
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        $hasForeignKey = $this->hasCategoryForeignKey();

        Schema::table('meals', function (Blueprint $table) use ($hasForeignKey) {
            if ($hasForeignKey) {
                $table->dropForeign(['category_id']);
            }

            if (Schema::hasColumn('meals', 'category_id')) {
                $table->dropColumn('category_id');
            }
        });
    }

    protected function hasCategoryForeignKey(): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'meals')
            ->where('COLUMN_NAME', 'category_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }
}
