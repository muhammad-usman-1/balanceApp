<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSubcrption extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'user_subcrptions';

    public const PAYMENT_SELECT = [
        'pending' => 'Pending',
        'paid'    => 'Paid',
    ];

    public const STATUS_SELECT = [
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'selected_days',
        'user_address_id',
        'start_date',
        'end_date',
        'user_id',
        'subcrption_plans_id',
        'duration_id',
        'price',
        'currency',
        'payment',
        'payment_reference',
        'payment_gateway',
        'card_last_four',
        'card_brand',
        'payment_meta',
        'status',
        'is_personalized',
        'protein',
        'carbs',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'payment_meta' => 'array',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getStartDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value ? Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getEndDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = $value ? Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d') : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subcrption_plans()
    {
        return $this->belongsTo(SubcrptionPlan::class, 'subcrption_plans_id');
    }

    public function duration()
    {
        return $this->belongsTo(Duration::class, 'duration_id');
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function subscription_days()
    {
        return $this->hasMany(SubscriptionDay::class, 'user_subcrptions_id');
    }
}
