@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.subscriptionMeal.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.subscription-meals.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="subscription_plan_days_id">{{ trans('cruds.subscriptionMeal.fields.subscription_plan_days') }}</label>
                <select class="form-control select2 {{ $errors->has('subscription_plan_days') ? 'is-invalid' : '' }}" name="subscription_plan_days_id" id="subscription_plan_days_id" required>
                    @foreach($subscription_plan_days as $id => $entry)
                        <option value="{{ $id }}" {{ old('subscription_plan_days_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('subscription_plan_days'))
                    <span class="text-danger">{{ $errors->first('subscription_plan_days') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subscriptionMeal.fields.subscription_plan_days_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="meal_id">{{ trans('cruds.subscriptionMeal.fields.meal') }}</label>
                <select class="form-control select2 {{ $errors->has('meal') ? 'is-invalid' : '' }}" name="meal_id" id="meal_id" required>
                    @foreach($meals as $id => $entry)
                        <option value="{{ $id }}" {{ old('meal_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('meal'))
                    <span class="text-danger">{{ $errors->first('meal') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subscriptionMeal.fields.meal_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="type_id">{{ trans('cruds.subscriptionMeal.fields.type') }}</label>
                <select class="form-control select2 {{ $errors->has('type') ? 'is-invalid' : '' }}" name="type_id" id="type_id" required>
                    @foreach($types as $id => $entry)
                        <option value="{{ $id }}" {{ old('type_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('type'))
                    <span class="text-danger">{{ $errors->first('type') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subscriptionMeal.fields.type_helper') }}</span>
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