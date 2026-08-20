<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionMeal extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'subscription_meals';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'subscription_days_id',
        'meal_id',
        'type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function subscription_days()
    {
        return $this->belongsTo(SubscriptionDay::class, 'subscription_days_id');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }

    /**
     * The customer's chosen extra options for this meal (per-subscription-meal).
     * The parent extra is derived from each ingredient's meal_extra_id.
     */
    public function selectedIngredients()
    {
        return $this->belongsToMany(
            MealExtraIngredient::class,
            'subscription_meal_extras',
            'subscription_meal_id',
            'meal_extra_ingredient_id'
        )->withTimestamps();
    }
}
