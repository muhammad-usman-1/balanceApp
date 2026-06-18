<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeliveryTimeSlot extends Model
{
    protected $fillable = ['value', 'label_en', 'label_ar', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public static function generateValue(string $labelEn): string
    {
        $base  = Str::slug($labelEn, '_');
        $value = $base;
        $i     = 2;
        while (static::where('value', $value)->exists()) {
            $value = $base . '_' . $i++;
        }
        return $value;
    }
}
