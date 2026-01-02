@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Coupon Usage History - {{ $coupon->coupon_code }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Coupons
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="info-box">
                            <div class="info-box-content">
                                <strong>Coupon Details:</strong><br>
                                <small>
                                    Code: <strong>{{ $coupon->coupon_code }}</strong> | 
                                    Type: <strong>{{ ucfirst($coupon->type) }}</strong> | 
                                    Value: <strong>
                                        @if($coupon->type === 'percentage')
                                            {{ number_format($coupon->value, 2) }}%
                                        @else
                                            {{ number_format($coupon->value, 2) }}
                                        @endif
                                    </strong> | 
                                    Usage Limit: <strong>
                                        @if($coupon->usage_limit_per_user)
                                            {{ $coupon->usage_limit_per_user }} per user
                                        @else
                                            Unlimited
                                        @endif
                                    </strong> | 
                                    Total Uses: <strong>{{ $usages->count() }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                @if($userUsageCounts->count() > 0)
                <div class="mb-4">
                    <h5>Usage Summary by User</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Usage Count</th>
                                    <th>Limit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userUsageCounts as $usageCount)
                                <tr>
                                    <td>{{ $usageCount->user_id }}</td>
                                    <td>{{ $usageCount->user->name ?? 'N/A' }}</td>
                                    <td>{{ $usageCount->user->email ?? 'N/A' }}</td>
                                    <td>{{ $usageCount->user->mobile ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-primary">{{ $usageCount->usage_count }}</span>
                                    </td>
                                    <td>
                                        @if($coupon->usage_limit_per_user)
                                            {{ $coupon->usage_limit_per_user }}
                                        @else
                                            <span class="text-muted">Unlimited</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($coupon->usage_limit_per_user && $usageCount->usage_count >= $coupon->usage_limit_per_user)
                                            <span class="badge badge-danger">Limit Reached</span>
                                        @else
                                            <span class="badge badge-success">Can Use</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <h5>Detailed Usage History</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Discount Amount</th>
                                <th>Order Amount</th>
                                <th>Notes</th>
                                <th>Used At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usages as $usage)
                            <tr>
                                <td>{{ $usage->id }}</td>
                                <td>{{ $usage->user->name ?? 'N/A' }}</td>
                                <td>{{ $usage->user->email ?? 'N/A' }}</td>
                                <td>{{ $usage->user->mobile ?? 'N/A' }}</td>
                                <td>
                                    @if($usage->discount_amount)
                                        {{ number_format($usage->discount_amount, 2) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($usage->order_amount)
                                        {{ number_format($usage->order_amount, 2) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $usage->notes ?? '-' }}</td>
                                <td>{{ $usage->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No usage history found for this coupon.</td>
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

