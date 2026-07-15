@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-clipboard-list mr-2" style="color:#16a34a;"></i> Subscriptions</h3>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success" style="margin:16px 20px 10px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="idx-flash idx-flash-error" style="margin:16px 20px 0;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Personalized</th>
                        <th>Coupon</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userSubcrptions as $sub)
                    <tr>
                        <td style="font-weight:600; color:#111827;">#{{ $sub->id }}</td>
                        <td>
                            <span style="font-weight:600; color:#111827;">{{ $sub->user->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="idx-chip chip-violet">{{ $sub->subcrption_plans->title ?? '—' }}</span>
                        </td>
                        <td style="white-space:nowrap; font-size:.8rem;">{{ $sub->start_date ?? '—' }}</td>
                        <td style="white-space:nowrap; font-size:.8rem;">{{ $sub->end_date ?? '—' }}</td>
                        <td>
                            @if($sub->payment === 'paid')
                                <span class="idx-chip chip-green"><i class="fas fa-check" style="font-size:.55rem;"></i> Paid</span>
                                @if($sub->payment_gateway === 'cash')
                                    <div style="font-size:.68rem;color:#9ca3af;margin-top:2px;">via Cash on Delivery</div>
                                @endif
                            @else
                                <span class="idx-chip chip-orange"><i class="fas fa-clock" style="font-size:.55rem;"></i> {{ ucfirst($sub->payment ?? 'pending') }}</span>
                                @if($sub->payment_gateway === 'cash')
                                    @can('user_subcrption_edit')
                                    <form action="{{ route('admin.user-subcrptions.mark-paid', $sub->id) }}" method="POST"
                                          class="mark-paid-form" style="display:inline-block;margin-top:4px;">
                                        @csrf
                                        <button type="button" class="idx-btn ib-green swal-mark-paid-btn" style="font-size:.68rem;padding:3px 8px;"
                                            data-name="{{ $sub->user->name ?? 'subscription #'.$sub->id }}">
                                            <i class="fas fa-money-check-alt"></i> Record Payment
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($sub->status === 'active')
                                <span class="idx-chip chip-green"><i class="fas fa-circle" style="font-size:.4rem;"></i> Active</span>
                            @else
                                <span class="idx-chip chip-gray">{{ ucfirst($sub->status ?? 'inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($sub->is_personalized)
                                <span class="idx-chip chip-blue">Yes</span>
                                <div style="font-size:.7rem;color:#9ca3af;margin-top:2px;">
                                    Protein: {{ $sub->protein ?? '—' }} · Carbs: {{ $sub->carbs ?? '—' }}
                                </div>
                            @else
                                <span style="font-size:.78rem; color:#9ca3af;">Standard plan</span>
                            @endif
                        </td>
                        <td>
                            @if($sub->coupon_code)
                                <span class="idx-chip chip-teal" style="font-family:monospace;font-size:.78rem;letter-spacing:.03em;">
                                    {{ $sub->coupon_code }}
                                </span>
                                @if($sub->discount_amount)
                                    <div style="font-size:.7rem;color:#9ca3af;margin-top:2px;">-{{ number_format($sub->discount_amount, 3) }} KWD</div>
                                @endif
                            @else
                                <span style="font-size:.78rem;color:#9ca3af;">No coupon applied</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            @can('user_subcrption_show')
                            <a href="{{ route('admin.user-subcrptions.show', $sub->id) }}" class="idx-btn ib-view">
                                <i class="fas fa-utensils"></i> Meals
                            </a>
                            @endcan
                            @can('user_subcrption_delete')
                            <form action="{{ route('admin.user-subcrptions.destroy', $sub->id) }}" method="POST"
                                  class="delete-form" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="button" class="idx-btn ib-del swal-delete-btn"
                                    data-name="{{ $sub->user->name ?? 'subscription #'.$sub->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="idx-empty">
                            <i class="fas fa-clipboard-list" style="font-size:2rem; color:#d1d5db;"></i><br>
                            No subscriptions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($userSubcrptions->hasPages())
        <div style="padding: 14px 20px; border-top: 1px solid #f3f4f6;">
            {{ $userSubcrptions->links('partials.pagination') }}
        </div>
        @endif
    </div>
</div>

@endsection
@section('scripts')
<script>
document.querySelectorAll('.swal-mark-paid-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var name = this.dataset.name || 'this subscription';
        var f    = this.closest('.mark-paid-form');
        Swal.fire({
            title: 'Record payment received?',
            html: 'Mark the cash-on-delivery payment for <strong>' + name + '</strong> as paid?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#15803d',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, mark as paid',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        }).then(function (result) {
            if (result.isConfirmed) f.submit();
        });
    });
});

document.querySelectorAll('.swal-delete-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var name = this.dataset.name || 'this subscription';
        var f    = this.closest('.delete-form');
        Swal.fire({
            title: 'Delete subscription?',
            html: 'Delete subscription for <strong>' + name + '</strong>?<br><span style="font-size:.85rem;color:#6b7280;">This action cannot be undone.</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        }).then(function (result) {
            if (result.isConfirmed) f.submit();
        });
    });
});
</script>
@endsection
