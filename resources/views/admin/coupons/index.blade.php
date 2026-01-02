@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Coupons</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Coupon
                    </a>
                </div>
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
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                <tr>
                    <th>ID</th>
                    <th>Coupon Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Usage Limit</th>
                    <th>Total Uses</th>
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
                        @if($coupon->usage_limit_per_user)
                            <span class="badge badge-info">{{ $coupon->usage_limit_per_user }} per user</span>
                        @else
                            <span class="badge badge-secondary">Unlimited</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-primary">{{ $coupon->usages_count ?? 0 }}</span>
                    </td>
                    <td>
                        @if(!$coupon->trashed())
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                                </a>
                                @if($coupon->usages_count > 0)
                                    <a href="{{ route('admin.coupons.usage-history', $coupon->id) }}" class="btn btn-sm btn-info" title="View Usage History">
                                        <i class="fas fa-history"></i> <span class="d-none d-md-inline">History</span>
                                    </a>
                                @endif
                                <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                        <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-muted">Deleted</span>
                        @endif
                    </td>
                </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No coupons found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

