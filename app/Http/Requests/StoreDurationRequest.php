<?php

namespace App\Http\Requests;

use App\Models\Duration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreDurationRequest extends FormRequest
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
            'no_of_weeks' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
        ];
    }
}
