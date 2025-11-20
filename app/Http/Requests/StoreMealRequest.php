<?php

namespace App\Http\Requests;

use App\Models\Meal;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreMealRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => [
                'string',
                'nullable',
            ],
            'description' => [
                'string',
                'nullable',
            ],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'calories' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'protein_g' => [
                'numeric',
            ],
            'fat_g' => [
                'numeric',
            ],
            'carbs_g' => [
                'numeric',
            ],
            'extras' => [
                'string',
                'nullable',
            ],
            'is_active' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
        ];
    }
}
