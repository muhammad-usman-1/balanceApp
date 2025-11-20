@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.duration.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.durations.update", [$duration->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="title">{{ trans('cruds.duration.fields.title') }}</label>
                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', $duration->title) }}">
                @if($errors->has('title'))
                    <span class="text-danger">{{ $errors->first('title') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.duration.fields.title_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="description">{{ trans('cruds.duration.fields.description') }}</label>
                <input class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" type="text" name="description" id="description" value="{{ old('description', $duration->description) }}">
                @if($errors->has('description'))
                    <span class="text-danger">{{ $errors->first('description') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.duration.fields.description_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="no_of_weeks">{{ trans('cruds.duration.fields.no_of_weeks') }}</label>
                <input class="form-control {{ $errors->has('no_of_weeks') ? 'is-invalid' : '' }}" type="number" name="no_of_weeks" id="no_of_weeks" value="{{ old('no_of_weeks', $duration->no_of_weeks) }}" step="1">
                @if($errors->has('no_of_weeks'))
                    <span class="text-danger">{{ $errors->first('no_of_weeks') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.duration.fields.no_of_weeks_helper') }}</span>
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