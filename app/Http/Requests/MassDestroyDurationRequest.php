<?php

namespace App\Http\Requests;

use App\Models\Duration;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyDurationRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('duration_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:durations,id',
        ];
    }
}
