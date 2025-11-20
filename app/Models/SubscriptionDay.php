<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionDay extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'subscription_days';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'user_subcrptions_id',
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

    public function user_subcrption()
    {
        return $this->belongsTo(UserSubcrption::class, 'user_subcrptions_id');
    }

    public function subscription_meals()
    {
        return $this->hasMany(SubscriptionMeal::class, 'subscription_days_id');
    }
}
