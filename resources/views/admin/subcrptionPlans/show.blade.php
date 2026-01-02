@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.subcrptionPlan.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.subcrption-plans.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.id') }}
                        </th>
                        <td>
                            {{ $subcrptionPlan->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.title') }}
                        </th>
                        <td>
                            {{ $subcrptionPlan->title }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.description') }}
                        </th>
                        <td>
                            {{ $subcrptionPlan->description }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Price per Week
                        </th>
                        <td>
                            {{ $subcrptionPlan->price }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.meal_count') }}
                        </th>
                        <td>
                            {{ $subcrptionPlan->meal_count }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subcrptionPlan.fields.is_active') }}
                        </th>
                        <td>
                            <input type="checkbox" disabled="disabled" {{ $subcrptionPlan->is_active ? 'checked' : '' }}>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.subcrption-plans.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection
