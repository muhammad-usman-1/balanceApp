<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealGroup extends Model
{
    protected $fillable = ['name', 'weekly_limit'];

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }
}
