<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Anything that changes the customer menu (GET /api/v1/meals) clears its cache.
        $flushMenu = fn () => \App\Support\MealMenuCache::flush();
        foreach ([
            \App\Models\Meal::class,
            \App\Models\Category::class,
            \App\Models\MealGroup::class,
            \App\Models\MealExtra::class,
            \App\Models\MealExtraIngredient::class,
        ] as $model) {
            $model::saved($flushMenu);
            $model::deleted($flushMenu);
        }
        foreach (['saved', 'deleted'] as $event) {
            \Spatie\MediaLibrary\MediaCollections\Models\Media::$event(function ($media) use ($flushMenu) {
                if ($media->model_type === \App\Models\Meal::class) {
                    $flushMenu();
                }
            });
        }

         // URL::forceScheme('https');
    }
}
