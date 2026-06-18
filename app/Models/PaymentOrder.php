<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentOrder extends Model
{
    protected $fillable = [
        'order_token',
        'user_id',
        'subscription_data',
        'amount',
        'currency',
        'payment_method',
        'status',
        'hesabe_payment_token',
        'hesabe_order_reference',
        'hesabe_response',
        'subscription_id',
    ];

    protected $casts = [
        'subscription_data' => 'array',
        'hesabe_response'   => 'array',
        'amount'            => 'decimal:3',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubcrption::class, 'subscription_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
