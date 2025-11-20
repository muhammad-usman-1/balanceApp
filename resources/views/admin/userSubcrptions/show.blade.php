@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.userSubcrption.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.user-subcrptions.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.id') }}
                        </th>
                        <td>
                            {{ $userSubcrption->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.selected_days') }}
                        </th>
                        <td>
                            {{ $userSubcrption->selected_days }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.start_date') }}
                        </th>
                        <td>
                            {{ $userSubcrption->start_date }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.end_date') }}
                        </th>
                        <td>
                            {{ $userSubcrption->end_date }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.user') }}
                        </th>
                        <td>
                            {{ $userSubcrption->user->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.subcrption_plans') }}
                        </th>
                        <td>
                            {{ $userSubcrption->subcrption_plans->title ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.duration') }}
                        </th>
                        <td>
                            {{ $userSubcrption->duration->title ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.price') }}
                        </th>
                        <td>
                            {{ $userSubcrption->price }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.payment') }}
                        </th>
                        <td>
                            {{ App\Models\UserSubcrption::PAYMENT_SELECT[$userSubcrption->payment] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\UserSubcrption::STATUS_SELECT[$userSubcrption->status] ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.user-subcrptions.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection