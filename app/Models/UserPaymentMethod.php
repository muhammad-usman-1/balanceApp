<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPaymentMethod extends EloquentModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'card_holder_name',
        'card_brand',
        'card_last_four',
        'card_expiry_month',
        'card_expiry_year',
        'card_number_encrypted',
        'hesabe_token',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

