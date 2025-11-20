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
        'subscription_plan_days_id',
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

    public function subscription_plan_days()
    {
        return $this->belongsTo(SubscriptionPlanDay::class, 'subscription_plan_days_id');
    }

    public function subscription_days()
    {
        return $this->belongsTo(SubscriptionDay::class, 'subscription_days_id');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }
}
