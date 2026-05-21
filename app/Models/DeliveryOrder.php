<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $fillable = [
        'user_subcrption_id',
        'subscription_day_id',
        'delivery_date',
        'status',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function subscription()
    {
        return $this->belongsTo(UserSubcrption::class, 'user_subcrption_id');
    }

    public function subscriptionDay()
    {
        return $this->belongsTo(SubscriptionDay::class, 'subscription_day_id');
    }
}
