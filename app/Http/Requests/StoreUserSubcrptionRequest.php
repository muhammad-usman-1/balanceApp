<?php

namespace App\Http\Requests;

use App\Models\UserSubcrption;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreUserSubcrptionRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('user_subcrption_create');
    }

    public function rules()
    {
        return [
            'selected_days' => [
                'string',
                'nullable',
            ],
            'start_date' => [
                'date_format:' . config('panel.date_format'),
                'nullable',
            ],
            'end_date' => [
                'date_format:' . config('panel.date_format'),
                'nullable',
            ],
            'user_id' => [
                'required',
                'integer',
            ],
            'subcrption_plans_id' => [
                'required',
                'integer',
            ],
            'duration_id' => [
                'required',
                'integer',
            ],
            'price' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
        ];
    }
}
