<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    public function toArray($request)
    {
        $image = $this->getFirstMedia('image');
        
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'category_name' => $this->category ? $this->category->name : null,
            'calories' => $this->calories,
            'protein_g' => $this->protein_g,
            'fat_g' => $this->fat_g,
            'carbs_g' => $this->carbs_g,
            'extras' => $this->extras,
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
