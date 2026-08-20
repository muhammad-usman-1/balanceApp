<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealExtraIngredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'meal_extra_ingredients';

    protected $fillable = [
        'meal_extra_id',
        'name',
        'name_ar',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mealExtra()
    {
        return $this->belongsTo(MealExtra::class);
    }
}
