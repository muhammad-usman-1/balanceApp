<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    public $table = 'coupon_usages';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'coupon_id',
        'user_id',
        'discount_amount',
        'order_amount',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'order_amount' => 'decimal:2',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
