@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.user.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.users.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr><th>{{ trans('cruds.user.fields.id') }}</th><td>{{ $user->id }}</td></tr>
                    <tr><th>{{ trans('cruds.user.fields.name') }}</th><td>{{ $user->name }}</td></tr>
                    <tr><th>{{ trans('cruds.user.fields.email') }}</th><td>{{ $user->email }}</td></tr>
                    <tr><th>Country Code</th><td>{{ $user->country_code }}</td></tr>
                    <tr><th>{{ trans('cruds.user.fields.email_verified_at') }}</th><td>{{ $user->email_verified_at }}</td></tr>
                    <tr>
                        <th>{{ trans('cruds.user.fields.roles') }}</th>
                        <td>
                            @foreach($user->roles as $key => $roles)
                                <span class="label label-info">{{ $roles->title }}</span>
                            @endforeach
                        </td>
                    </tr>
                    <tr><th>{{ trans('cruds.user.fields.otp') }}</th><td>{{ $user->otp }}</td></tr>
                    <tr><th>{{ trans('cruds.user.fields.mobile') }}</th><td>{{ $user->mobile }}</td></tr>
                    <tr><th>{{ trans('cruds.user.fields.gender') }}</th><td>{{ $user->gender }}</td></tr>
                    <tr><th>{{ trans('cruds.user.fields.height') }}</th><td>{{ $user->height }}</td></tr>
                    <tr><th>{{ trans('cruds.user.fields.weight') }}</th><td>{{ $user->weight }}</td></tr>
                    <tr><th>{{ trans('cruds.user.fields.dob') }}</th><td>{{ $user->dob }}</td></tr>
                    <tr><th>Activity Level</th><td>{{ $user->activity_level }}</td></tr>
                    <tr><th>Goal</th><td>{{ $user->goal }}</td></tr>
                    <tr><th>Has Food Allergies</th><td>{{ $user->has_food_allergies ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Allergies</th><td>{{ $user->allergies ? implode(', ', $user->allergies) : '' }}</td></tr>
                    <tr><th>OTP Expires At</th><td>{{ $user->otp_expires_at }}</td></tr>
                    <tr><th>Created At</th><td>{{ $user->created_at }}</td></tr>
                    <tr><th>Updated At</th><td>{{ $user->updated_at }}</td></tr>
                </tbody>
            </table>
            <hr>
            <h4>User Addresses</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Phone</th>
                        <th>Area</th>
                        <th>Street</th>
                        <th>Building</th>
                        <th>Floor/Apartment</th>
                        <th>Preferred Slot</th>
                        <th>Primary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->addresses as $address)
                        <tr>
                            <td>{{ $address->category }}</td>
                            <td>{{ $address->phone_number }}</td>
                            <td>{{ $address->area }}</td>
                            <td>{{ $address->street }}</td>
                            <td>{{ $address->house_building }}</td>
                            <td>{{ $address->floor_apartment }}</td>
                            <td>{{ $address->preferred_delivery_slot }}</td>
                            <td>{{ $address->is_primary ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No addresses on file.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <h4>Payment Cards</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Card Holder</th>
                        <th>Brand</th>
                        <th>Last 4</th>
                        <th>Expiry</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->paymentMethods as $card)
                        <tr>
                            <td>{{ $card->card_holder_name }}</td>
                            <td>{{ $card->card_brand }}</td>
                            <td>{{ $card->card_last_four }}</td>
                            <td>{{ $card->card_expiry_month }}/{{ $card->card_expiry_year }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No payment cards on file.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.users.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection