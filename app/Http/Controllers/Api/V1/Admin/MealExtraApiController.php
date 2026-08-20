<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\MealExtra;

class MealExtraApiController extends Controller
{
    /**
     * Catalog of all active extras with their active ingredient options.
     */
    public function index()
    {
        $extras = MealExtra::where('is_active', true)
            ->with(['activeIngredients'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($extra) {
                return [
                    'id'             => $extra->id,
                    'name'           => $extra->name,
                    'name_ar'        => $extra->name_ar,
                    'selection_type' => $extra->selection_type,
                    'is_required'    => $extra->is_required,
                    'ingredients'    => $extra->activeIngredients->map(fn($i) => [
                        'id'      => $i->id,
                        'name'    => $i->name,
                        'name_ar' => $i->name_ar,
                    ])->values(),
                ];
            });

        return response()->json(['meal_extras' => $extras]);
    }
}
