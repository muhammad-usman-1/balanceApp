@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-ticket-alt mr-2" style="color:#db2777;"></i> Coupons</h3>
        <a href="{{ route('admin.coupons.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Coupon
        </a>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="idx-flash idx-flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Status</th>
                        <th>Limit</th>
                        <th>Uses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $coupon->id }}</td>
                        <td>
                            <span style="font-weight:700;font-family:monospace;font-size:.88rem;letter-spacing:.04em;">
                                {{ $coupon->coupon_code }}
                            </span>
                            @if($coupon->trashed())
                                <span class="idx-chip chip-red ml-1">Deleted</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->type === 'fixed')
                                <span class="idx-chip chip-cyan">Fixed</span>
                            @else
                                <span class="idx-chip chip-violet">%</span>
                            @endif
                        </td>
                        <td style="font-weight:600;">
                            {{ number_format($coupon->value, 2) }}{{ $coupon->type === 'percentage' ? '%' : ' KWD' }}
                        </td>
                        <td style="color:#6b7280;font-size:.78rem;white-space:nowrap;">{{ $coupon->start_date->format('d M Y') }}</td>
                        <td style="color:#6b7280;font-size:.78rem;white-space:nowrap;">{{ $coupon->end_date->format('d M Y') }}</td>
                        <td>
                            @if($coupon->status === 'active')
                                <span class="idx-chip chip-green"><i class="fas fa-circle" style="font-size:.4rem;"></i> Active</span>
                            @else
                                <span class="idx-chip chip-gray">{{ ucfirst($coupon->status) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->usage_limit_per_user)
                                <span class="idx-chip chip-blue">{{ $coupon->usage_limit_per_user }}/user</span>
                            @else
                                <span style="color:#9ca3af;font-size:.78rem;">Unlimited</span>
                            @endif
                        </td>
                        <td>
                            <span class="idx-chip chip-teal">{{ $coupon->usages_count ?? 0 }}</span>
                        </td>
                        <td style="white-space:nowrap;">
                            @if(!$coupon->trashed())
                                <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="idx-btn ib-edit">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                @if(($coupon->usages_count ?? 0) > 0)
                                <a href="{{ route('admin.coupons.usage-history', $coupon->id) }}" class="idx-btn ib-purple">
                                    <i class="fas fa-history"></i> History
                                </a>
                                @endif
                                <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                                </form>
                            @else
                                <span style="color:#9ca3af;font-size:.78rem;">Deleted</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="idx-empty"><i class="fas fa-ticket-alt"></i><br>No coupons found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
