<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    public function toArray($request)
    {
        $image = $this->getFirstMedia('image');

        // Group this meal's enabled ingredient options by their extra, then output
        // each attached extra with only the options available for this meal.
        $ingredientsByExtra = $this->availableIngredients->groupBy('meal_extra_id');
        $extras = $this->mealExtras->map(function ($extra) use ($ingredientsByExtra) {
            $options = ($ingredientsByExtra->get($extra->id) ?? collect())
                ->map(fn($i) => [
                    'id'      => $i->id,
                    'name'    => $i->name,
                    'name_ar' => $i->name_ar,
                ])->values();

            return [
                'id'             => $extra->id,
                'name'           => $extra->name,
                'name_ar'        => $extra->name_ar,
                'selection_type' => $extra->selection_type,
                'is_required'    => $extra->pivot->is_required !== null
                    ? (bool) $extra->pivot->is_required
                    : (bool) $extra->is_required,
                // Max options the customer may pick from this extra for this meal.
                // single => 1; multiple => the per-meal cap, or null for no limit.
                'max_select'     => $extra->selection_type === 'single'
                    ? 1
                    : ($extra->pivot->max_select !== null ? (int) $extra->pivot->max_select : null),
                'ingredients'    => $options,
            ];
        })->values();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'category_id' => $this->category_id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'name_ar' => $this->category->name_ar,
            ] : null,
            'category_name' => $this->category ? $this->category->name : null,
            'category_name_ar' => $this->category ? $this->category->name_ar : null,
            'calories' => $this->calories,
            'protein_g' => $this->protein_g,
            'fat_g' => $this->fat_g,
            'carbs_g' => $this->carbs_g,
            'extras' => $this->extras, // allergens / free-text note (existing column)
            'is_active' => $this->is_active,
            'type' => $this->type,
            'image' => $image ? [
                'id' => $image->id,
                'url' => $image->getUrl(),
                'thumb_url' => $image->getUrl('thumb') ?: $image->getUrl(),
                'preview_url' => $image->getUrl('preview') ?: $image->getUrl(),
                'file_name' => $image->file_name,
                'mime_type' => $image->mime_type,
                'size' => $image->size,
            ] : null,
            'image_url' => $image ? $image->getUrl() : null,
            'image_thumb_url' => $image ? ($image->getUrl('thumb') ?: $image->getUrl()) : null,
            'meal_extras' => $extras, // extra categories + their available options (new)
            'meal_group_id' => $this->meal_group_id,
            'meal_group' => $this->mealGroup ? [
                'id' => $this->mealGroup->id,
                'name' => $this->mealGroup->name,
                'weekly_limit' => $this->mealGroup->weekly_limit,
            ] : null,
            'weekly_limit' => $this->mealGroup?->weekly_limit ?? null,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : null,
        ];
    }
}
