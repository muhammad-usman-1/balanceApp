<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The per-meal weekly restriction system (meal_restrictions) is replaced by
 * Meal Groups (meal_groups + meals.meal_group_id), which apply a shared
 * weekly limit across a whole group of meals instead of a single meal.
 */
return new class extends Migration
{
    public function up()
    {
        // Preserve existing restrictions as one-meal groups so admins don't
        // silently lose configured limits — each becomes its own group,
        // which can be freely merged/reorganized afterward in the new UI.
        if (Schema::hasTable('meal_restrictions')) {
            $restrictions = DB::table('meal_restrictions')->get();

            foreach ($restrictions as $restriction) {
                $meal = DB::table('meals')->find($restriction->meal_id);
                if (! $meal) {
                    continue;
                }

                $groupId = DB::table('meal_groups')->insertGetId([
                    'name'         => $meal->title . ' (migrated limit)',
                    'weekly_limit' => $restriction->weekly_limit,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                DB::table('meals')->where('id', $meal->id)->update(['meal_group_id' => $groupId]);
            }
        }

        Schema::dropIfExists('meal_restrictions');
    }

    public function down()
    {
        Schema::create('meal_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_id')->unique()->constrained('meals')->onDelete('cascade');
            $table->unsignedTinyInteger('weekly_limit')->default(2);
            $table->timestamps();
        });
    }
};
