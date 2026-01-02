<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'coupon_code',
        'type',
        'value',
        'start_date',
        'end_date',
        'status',
        'usage_limit_per_user',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'usage_limit_per_user' => 'integer',
    ];

    /**
     * Get all usages of this coupon
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id');
    }

    /**
     * Get usage count for a specific user
     * 
     * @param int $userId
     * @return int
     */
    public function getUserUsageCount($userId)
    {
        return $this->usages()->where('user_id', $userId)->count();
    }

    /**
     * Check if a user can use this coupon
     * 
     * @param int $userId
     * @return array ['can_use' => bool, 'message' => string]
     */
    public function canUserUse($userId)
    {
        // Check if coupon is active
        if ($this->status !== 'active') {
            return [
                'can_use' => false,
                'message' => 'This coupon is not active.',
            ];
        }

        // Check if coupon is within valid date range
        $now = now()->format('Y-m-d');
        if ($now < $this->start_date->format('Y-m-d') || $now > $this->end_date->format('Y-m-d')) {
            return [
                'can_use' => false,
                'message' => 'This coupon is not valid for the current date.',
            ];
        }

        // If no usage limit is set, user can use it
        if ($this->usage_limit_per_user === null) {
            return [
                'can_use' => true,
                'message' => 'Coupon is valid.',
            ];
        }

        // Check if user has reached the usage limit
        $usageCount = $this->getUserUsageCount($userId);
        if ($usageCount >= $this->usage_limit_per_user) {
            return [
                'can_use' => false,
                'message' => "You have reached the maximum usage limit ({$this->usage_limit_per_user}) for this coupon.",
            ];
        }

        return [
            'can_use' => true,
            'message' => 'Coupon is valid.',
            'remaining_uses' => $this->usage_limit_per_user - $usageCount,
        ];
    }

    /**
     * Record a coupon usage
     * 
     * @param int $userId
     * @param float|null $discountAmount
     * @param float|null $orderAmount
     * @param string|null $notes
     * @return CouponUsage
     */
    public function recordUsage($userId, $discountAmount = null, $orderAmount = null, $notes = null)
    {
        return CouponUsage::create([
            'coupon_id' => $this->id,
            'user_id' => $userId,
            'discount_amount' => $discountAmount,
            'order_amount' => $orderAmount,
            'notes' => $notes,
        ]);
    }
}

