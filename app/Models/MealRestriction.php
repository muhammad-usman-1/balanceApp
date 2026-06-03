<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealRestriction extends Model
{
    protected $fillable = ['meal_id', 'weekly_limit'];

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
