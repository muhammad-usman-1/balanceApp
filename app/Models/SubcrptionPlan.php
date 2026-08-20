<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubcrptionPlan extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'subcrption_plans';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'description_ar',
        'price',
        'meal_count',
        'snack_count',
        'min_days',
        'max_days',
        'no_of_weeks',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
