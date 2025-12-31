<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'delivery_charges',
        'status',
    ];

    protected $casts = [
        'delivery_charges' => 'decimal:2',
    ];

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'area_branch');
    }
}

