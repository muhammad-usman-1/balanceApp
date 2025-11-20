@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.subcrptionPlan.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.subcrption-plans.update", [$subcrptionPlan->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="title">{{ trans('cruds.subcrptionPlan.fields.title') }}</label>
                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', $subcrptionPlan->title) }}">
                @if($errors->has('title'))
                    <span class="text-danger">{{ $errors->first('title') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subcrptionPlan.fields.title_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="description">{{ trans('cruds.subcrptionPlan.fields.description') }}</label>
                <input class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" type="text" name="description" id="description" value="{{ old('description', $subcrptionPlan->description) }}">
                @if($errors->has('description'))
                    <span class="text-danger">{{ $errors->first('description') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subcrptionPlan.fields.description_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="price">{{ trans('cruds.subcrptionPlan.fields.price') }}</label>
                <input class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}" type="number" name="price" id="price" value="{{ old('price', $subcrptionPlan->price) }}" step="0.01">
                @if($errors->has('price'))
                    <span class="text-danger">{{ $errors->first('price') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subcrptionPlan.fields.price_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="meal_count">{{ trans('cruds.subcrptionPlan.fields.meal_count') }}</label>
                <input class="form-control {{ $errors->has('meal_count') ? 'is-invalid' : '' }}" type="number" name="meal_count" id="meal_count" value="{{ old('meal_count', $subcrptionPlan->meal_count) }}" step="1">
                @if($errors->has('meal_count'))
                    <span class="text-danger">{{ $errors->first('meal_count') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subcrptionPlan.fields.meal_count_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="snack_count">{{ trans('cruds.subcrptionPlan.fields.snack_count') }}</label>
                <input class="form-control {{ $errors->has('snack_count') ? 'is-invalid' : '' }}" type="number" name="snack_count" id="snack_count" value="{{ old('snack_count', $subcrptionPlan->snack_count) }}" step="1">
                @if($errors->has('snack_count'))
                    <span class="text-danger">{{ $errors->first('snack_count') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subcrptionPlan.fields.snack_count_helper') }}</span>
            </div>
            <div class="form-group">
                <div class="form-check {{ $errors->has('is_active') ? 'is-invalid' : '' }}">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $subcrptionPlan->is_active || old('is_active', 0) === 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">{{ trans('cruds.subcrptionPlan.fields.is_active') }}</label>
                </div>
                @if($errors->has('is_active'))
                    <span class="text-danger">{{ $errors->first('is_active') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subcrptionPlan.fields.is_active_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection