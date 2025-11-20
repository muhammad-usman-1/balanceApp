@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.subscriptionPlanDay.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.subscription-plan-days.update", [$subscriptionPlanDay->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="subscription_plans_id">{{ trans('cruds.subscriptionPlanDay.fields.subscription_plans') }}</label>
                <select class="form-control select2 {{ $errors->has('subscription_plans') ? 'is-invalid' : '' }}" name="subscription_plans_id" id="subscription_plans_id" required>
                    @foreach($subscription_plans as $id => $entry)
                        <option value="{{ $id }}" {{ (old('subscription_plans_id') ? old('subscription_plans_id') : $subscriptionPlanDay->subscription_plans->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('subscription_plans'))
                    <span class="text-danger">{{ $errors->first('subscription_plans') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subscriptionPlanDay.fields.subscription_plans_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.subscriptionPlanDay.fields.day') }}</label>
                <select class="form-control {{ $errors->has('day') ? 'is-invalid' : '' }}" name="day" id="day">
                    <option value disabled {{ old('day', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\SubscriptionPlanDay::DAY_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('day', $subscriptionPlanDay->day) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('day'))
                    <span class="text-danger">{{ $errors->first('day') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subscriptionPlanDay.fields.day_helper') }}</span>
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