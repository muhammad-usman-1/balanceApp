<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlanDay extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'subscription_plan_days';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'subscription_plans_id',
        'day',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const DAY_SELECT = [
        'monday'    => 'monday',
        'tuesday'   => 'tuesday',
        'wednesday' => 'wednesday',
        'thursday'  => 'thursday',
        'friday'    => 'friday',
        'saturday'  => 'saturday',
        'sunday'    => 'sunday',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function subscription_plans()
    {
        return $this->belongsTo(SubcrptionPlan::class, 'subscription_plans_id');
    }
}
