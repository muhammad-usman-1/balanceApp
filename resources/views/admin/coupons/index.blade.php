@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Coupons</h3>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary float-right">Add Coupon</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Coupon Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                <tr class="{{ $coupon->trashed() ? 'table-secondary' : '' }}">
                    <td>{{ $coupon->id }}</td>
                    <td>
                        <strong>{{ $coupon->coupon_code }}</strong>
                        @if($coupon->trashed())
                            <span class="badge badge-warning">Deleted</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $coupon->type === 'fixed' ? 'info' : 'success' }}">
                            {{ ucfirst($coupon->type) }}
                        </span>
                    </td>
                    <td>
                        @if($coupon->type === 'percentage')
                            {{ number_format($coupon->value, 2) }}%
                        @else
                            {{ number_format($coupon->value, 2) }}
                        @endif
                    </td>
                    <td>{{ $coupon->start_date->format('Y-m-d') }}</td>
                    <td>{{ $coupon->end_date->format('Y-m-d') }}</td>
                    <td>
                        <span class="badge badge-{{ $coupon->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($coupon->status) }}
                        </span>
                    </td>
                    <td>
                        @if(!$coupon->trashed())
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        @else
                            <span class="text-muted">Deleted</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No coupons found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

