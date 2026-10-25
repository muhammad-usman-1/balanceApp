<?php

namespace App\Http\Resources\Admin;

use Illuminate\Support\Arr;

/**
 * Slim meal shape for the menu list (GET /api/v1/meals). Same as MealResource
 * minus fields the app's list never uses; full detail stays on GET /meals/{id}.
 */
class MealListResource extends MealResource
{
    public function toArray($request)
    {
        return Arr::except(parent::toArray($request), [
            'created_at',
            'updated_at',
            'deleted_at',
        ]);
    }
}
