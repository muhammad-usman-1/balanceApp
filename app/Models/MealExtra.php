<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealExtra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'meal_extras';

    protected $fillable = [
        'name',
        'name_ar',
        'selection_type',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public const SELECTION_TYPES = [
        'single'   => 'Single choice',
        'multiple' => 'Multiple choice',
    ];

    public function ingredients()
    {
        return $this->hasMany(MealExtraIngredient::class)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function activeIngredients()
    {
        return $this->ingredients()->where('is_active', true);
    }

    public function meals()
    {
        return $this->belongsToMany(Meal::class, 'meal_meal_extra')
            ->withPivot('is_required', 'max_select', 'sort_order')
            ->withTimestamps();
    }
}
