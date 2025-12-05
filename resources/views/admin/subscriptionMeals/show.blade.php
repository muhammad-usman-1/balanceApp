@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.subscriptionMeal.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.subscription-meals.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.subscriptionMeal.fields.id') }}
                        </th>
                        <td>
                            {{ $subscriptionMeal->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subscriptionMeal.fields.subscription_days') }}
                        </th>
                        <td>
                            {{ $subscriptionMeal->subscription_days->day ?? '' }}
                            @if($subscriptionMeal->subscription_days)
                                <small class="text-muted">(User Subscription #{{ $subscriptionMeal->subscription_days->user_subcrptions_id }})</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subscriptionMeal.fields.meal') }}
                        </th>
                        <td>
                            {{ $subscriptionMeal->meal->title ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subscriptionMeal.fields.type') }}
                        </th>
                        <td>
                            {{ $subscriptionMeal->type ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.subscription-meals.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection