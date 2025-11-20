@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.meal.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.meals.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.id') }}
                        </th>
                        <td>
                            {{ $meal->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.title') }}
                        </th>
                        <td>
                            {{ $meal->title }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.description') }}
                        </th>
                        <td>
                            {{ $meal->description }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.image') }}
                        </th>
                        <td>
                            @if($meal->image)
                                <a href="{{ $meal->image->getUrl() }}" target="_blank">
                                    {{ trans('global.view_file') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.category') }}
                        </th>
                        <td>
                            {{ $meal->category }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.calories') }}
                        </th>
                        <td>
                            {{ $meal->calories }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.protein_g') }}
                        </th>
                        <td>
                            {{ $meal->protein_g }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.fat_g') }}
                        </th>
                        <td>
                            {{ $meal->fat_g }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.carbs_g') }}
                        </th>
                        <td>
                            {{ $meal->carbs_g }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.extras') }}
                        </th>
                        <td>
                            {{ $meal->extras }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.is_active') }}
                        </th>
                        <td>
                            {{ $meal->is_active }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.meal.fields.type') }}
                        </th>
                        <td>
                            {{ App\Models\Meal::TYPE_RADIO[$meal->type] ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.meals.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection