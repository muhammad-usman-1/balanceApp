@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.subscriptionPlanDay.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.subscription-plan-days.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.subscriptionPlanDay.fields.id') }}
                        </th>
                        <td>
                            {{ $subscriptionDay->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subscriptionPlanDay.fields.user_subcrption') }}
                        </th>
                        <td>
                            User Subscription #{{ $subscriptionDay->user_subcrptions_id }}
                            @if($subscriptionDay->user_subcrption)
                                <br><small class="text-muted">User: {{ $subscriptionDay->user_subcrption->user->name ?? 'N/A' }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subscriptionPlanDay.fields.subscription_plans') }}
                        </th>
                        <td>
                            {{ $subscriptionDay->user_subcrption->subcrption_plans->title ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.subscriptionPlanDay.fields.day') }}
                        </th>
                        <td>
                            {{ ucfirst(App\Models\SubscriptionDay::DAY_SELECT[$subscriptionDay->day] ?? $subscriptionDay->day) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.subscription-plan-days.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection