@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.duration.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.durations.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.duration.fields.id') }}
                        </th>
                        <td>
                            {{ $duration->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.duration.fields.title') }}
                        </th>
                        <td>
                            {{ $duration->title }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.duration.fields.description') }}
                        </th>
                        <td>
                            {{ $duration->description }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.duration.fields.no_of_weeks') }}
                        </th>
                        <td>
                            {{ $duration->no_of_weeks }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.durations.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection