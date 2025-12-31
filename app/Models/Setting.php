<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_time_slot_1_en',
        'delivery_time_slot_2_en',
        'payment_knet',
        'payment_credit_card',
        'payment_cash',
    ];

    protected $casts = [
        'payment_knet' => 'boolean',
        'payment_credit_card' => 'boolean',
        'payment_cash' => 'boolean',
    ];

    /**
     * Get the first (and only) settings record
     */
    public static function firstOrCreateDefault()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'delivery_time_slot_1_en' => '4pm to 8pm',
                'delivery_time_slot_2_en' => '8pm to 12am',
                'payment_knet' => true,
                'payment_credit_card' => true,
                'payment_cash' => true,
            ]
        );
    }
}

