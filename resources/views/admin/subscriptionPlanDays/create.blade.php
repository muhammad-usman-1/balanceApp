@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.subscriptionPlanDay.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.subscription-plan-days.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="user_subcrptions_id">{{ trans('cruds.subscriptionPlanDay.fields.user_subcrption') }}</label>
                <select class="form-control select2 {{ $errors->has('user_subcrptions_id') ? 'is-invalid' : '' }}" name="user_subcrptions_id" id="user_subcrptions_id" required>
                    @foreach($user_subscriptions as $id => $entry)
                        <option value="{{ $id }}" {{ old('user_subcrptions_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('user_subcrptions_id'))
                    <span class="text-danger">{{ $errors->first('user_subcrptions_id') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.subscriptionPlanDay.fields.user_subcrption_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="day">{{ trans('cruds.subscriptionPlanDay.fields.day') }}</label>
                <select class="form-control {{ $errors->has('day') ? 'is-invalid' : '' }}" name="day" id="day" required>
                    <option value disabled {{ old('day', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\SubscriptionDay::DAY_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('day', '') === (string) $key ? 'selected' : '' }}>{{ ucfirst($label) }}</option>
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