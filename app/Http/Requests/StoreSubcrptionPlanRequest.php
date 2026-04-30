<?php

namespace App\Http\Requests;

use App\Models\SubcrptionPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreSubcrptionPlanRequest extends FormRequest
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
            'price' => [
                'numeric',
            ],
            'meal_count' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'snack_count' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'min_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:6',
            ],
            'max_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:7',
                'gte:min_days',
            ],
            'no_of_weeks' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
