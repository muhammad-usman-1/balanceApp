@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.userSubcrption.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.user-subcrptions.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="selected_days">{{ trans('cruds.userSubcrption.fields.selected_days') }}</label>
                <input class="form-control {{ $errors->has('selected_days') ? 'is-invalid' : '' }}" type="text" name="selected_days" id="selected_days" value="{{ old('selected_days', '') }}">
                @if($errors->has('selected_days'))
                    <span class="text-danger">{{ $errors->first('selected_days') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.userSubcrption.fields.selected_days_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="start_date">{{ trans('cruds.userSubcrption.fields.start_date') }}</label>
                <input class="form-control date {{ $errors->has('start_date') ? 'is-invalid' : '' }}" type="text" name="start_date" id="start_date" value="{{ old('start_date') }}">
                @if($errors->has('start_date'))
                    <span class="text-danger">{{ $errors->first('start_date') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.userSubcrption.fields.start_date_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="end_date">{{ trans('cruds.userSubcrption.fields.end_date') }}</label>
                <input class="form-control date {{ $errors->has('end_date') ? 'is-invalid' : '' }}" type="text" name="end_date" id="end_date" value="{{ old('end_date') }}">
                @if($errors->has('end_date'))
                    <span class="text-danger">{{ $errors->first('end_date') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.userSubcrption.fields.end_date_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="user_id">{{ trans('cruds.userSubcrption.fields.user') }}</label>
                <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                    @foreach($users as $id => $entry)
                        <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('user'))
                    <span class="text-danger">{{ $errors->first('user') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.userSubcrption.fields.user_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="subcrption_plans_id">{{ trans('cruds.userSubcrption.fields.subcrption_plans') }}</label>
                <select class="form-control select2 {{ $errors->has('subcrption_plans') ? 'is-invalid' : '' }}" name="subcrption_plans_id" id="subcrption_plans_id" required>
                    @foreach($subcrption_plans as $id => $entry)
                        <option value="{{ $id }}" {{ old('subcrption_plans_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('subcrption_plans'))
                    <span class="text-danger">{{ $errors->first('subcrption_plans') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.userSubcrption.fields.subcrption_plans_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="duration_id">{{ trans('cruds.userSubcrption.fields.duration') }}</label>
                <select class="form-control select2 {{ $errors->has('duration') ? 'is-invalid' : '' }}" name="duration_id" id="duration_id" required>
                    @foreach($durations as $id => $entry)
                        <option value="{{ $id }}" {{ old('duration_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('duration'))
                    <span class="text-danger">{{ $errors->first('duration') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.userSubcrption.fields.duration_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="price">{{ trans('cruds.userSubcrption.fields.price') }}</label>
                <input class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}" type="number" name="price" id="price" value="{{ old('price', '') }}" step="1">
                @if($errors->has('price'))
                    <span class="text-danger">{{ $errors->first('price') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.userSubcrption.fields.price_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.userSubcrption.fields.payment') }}</label>
                <select class="form-control {{ $errors->has('payment') ? 'is-invalid' : '' }}" name="payment" id="payment">
                    <option value disabled {{ old('payment', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\UserSubcrption::PAYMENT_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('payment', 'pending') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('payment'))
                    <span class="text-danger">{{ $errors->first('payment') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.userSubcrption.fields.payment_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.userSubcrption.fields.status') }}</label>
                <select class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status">
                    <option value disabled {{ old('status', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\UserSubcrption::STATUS_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('status', '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('status'))
                    <span class="text-danger">{{ $errors->first('status') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.userSubcrption.fields.status_helper') }}</span>
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