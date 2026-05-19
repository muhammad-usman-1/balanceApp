<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProteinOption extends Model
{
    protected $fillable = [
        'protein_grams',
        'extra_price_per_meal',
        'is_active',
    ];

    protected $casts = [
        'protein_grams'        => 'integer',
        'extra_price_per_meal' => 'decimal:3',
        'is_active'            => 'boolean',
    ];
}
