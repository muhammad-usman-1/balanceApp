<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Meal extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, HasFactory;

    public $table = 'meals';

    protected $appends = [
        'image',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TYPE_RADIO = [
        'is snack' => 'is snack',
        'is meal'  => 'is meal',
    ];

    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'category_id',
        'meal_group_id',
        'calories',
        'protein_g',
        'fat_g',
        'carbs_g',
        'extras',
        'is_active',
        'type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function mealGroup()
    {
        return $this->belongsTo(MealGroup::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->useDisk('meals')
            ->useFallbackUrl('/images/placeholder.png')
            ->useFallbackPath(public_path('/images/placeholder.png'));
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function getImageAttribute()
    {
        return $this->getMedia('image')->last();
    }
}
