@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
.us-filter-bar {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    padding: 10px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
}
.us-search { position: relative; }
.us-search input {
    padding: 5px 12px 5px 30px; border-radius: 20px;
    border: 1px solid #e5e7eb; font-size: .82rem; height: 32px; width: 240px; outline: none;
}
.us-search input:focus { border-color: #111827; box-shadow: 0 0 0 2px rgba(17,24,39,.08); }
.us-search .fa-search { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: .72rem; }
.us-filter-bar select {
    padding: 5px 12px; border-radius: 20px; border: 1px solid #e5e7eb;
    font-size: .82rem; height: 32px; outline: none; background: #fff; color: #374151;
}
.us-filter-bar select:focus { border-color: #111827; box-shadow: 0 0 0 2px rgba(17,24,39,.08); }
.us-info-note {
    display: flex; align-items: flex-start; gap: 8px;
    margin: 14px 20px 10px; padding: 10px 14px; background: #eff6ff;
    border: 1px solid #bfdbfe; border-radius: 8px; font-size: .78rem; color: #1e40af;
}
.us-info-note i { margin-top: 1px; }

/* Week days mini-view: which days are selected vs. off */
.us-days-row { display: flex; flex-wrap: wrap; gap: 3px; }
.us-day-dot {
    width: 22px; height: 20px; border-radius: 5px; display: inline-flex;
    align-items: center; justify-content: center;
    font-size: .62rem; font-weight: 700;
}
.us-day-on  { background: #dcfce7; color: #15803d; }
.us-day-off { background: #f3f4f6; color: #b0b6c0; }
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-clipboard-list mr-2" style="color:#16a34a;"></i> Subscriptions</h3>
    </div>

    <div class="us-info-note">
        <i class="fas fa-info-circle"></i>
        <span>
            <strong style="display:block; margin-bottom:3px;">Why the "Record Payment" button?</strong>
            It appears only next to Cash on Delivery subscriptions whose payment is still "Pending". It does
            not collect any money itself, it's a manual bookkeeping step for the admin to confirm the driver
            has physically collected the cash from the customer on delivery. Clicking it asks for confirmation,
            then marks that subscription's payment status as "Paid" and removes the button, since it's no
            longer needed once payment is recorded. This has no effect on card/KNET payments, which are marked
            paid automatically by the payment gateway.
        </span>
    </div>

    <form method="GET" action="{{ route('admin.user-subcrptions.index') }}" class="us-filter-bar">
        <div class="us-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or phone…">
        </div>
        <select name="status_filter" onchange="this.form.submit()">
            <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
            <option value="ended" {{ $statusFilter === 'ended' ? 'selected' : '' }}>Ended / Inactive</option>
            <option value="queued" {{ $statusFilter === 'queued' ? 'selected' : '' }}>Queued</option>
            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All</option>
        </select>
        @if($search || $statusFilter !== 'active')
        <a href="{{ route('admin.user-subcrptions.index') }}" class="idx-btn ib-gray" style="height:32px;">
            <i class="fas fa-times"></i> Clear
        </a>
        @endif
    </form>

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
                        <th>Days</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Personalized?</th>
                        <th>Coupon</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userSubcrptions as $sub)
                    @php
                        $rawEndDate = $sub->getRawOriginal('end_date');
                        $hasEnded = $rawEndDate && \Carbon\Carbon::parse($rawEndDate)->lt(\Carbon\Carbon::today());
                        $isEffectivelyEnded = $sub->status === 'inactive' || ($sub->status === 'active' && $hasEnded);
                    @endphp
                    <tr>
                        <td style="font-weight:600; color:#111827;">#{{ $sub->id }}</td>
                        <td>
                            <span style="font-weight:600; color:#111827;">{{ $sub->user->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="idx-chip chip-violet">{{ $sub->subcrption_plans->title ?? '—' }}</span>
                        </td>
                        <td style="min-width:170px;">
                            @php
                                $weekdays = ['monday'=>'Mo','tuesday'=>'Tu','wednesday'=>'We','thursday'=>'Th','friday'=>'Fr','saturday'=>'Sa','sunday'=>'Su'];
                                $selectedDays = $sub->selected_days
                                    ? array_map(fn($d) => strtolower(trim($d)), explode(',', $sub->selected_days))
                                    : [];
                            @endphp
                            @if(count($selectedDays))
                                <div class="us-days-row">
                                    @foreach($weekdays as $key => $abbr)
                                        <span class="us-day-dot {{ in_array($key, $selectedDays) ? 'us-day-on' : 'us-day-off' }}"
                                              title="{{ ucfirst($key) }} — {{ in_array($key, $selectedDays) ? 'delivery day' : 'off day' }}">
                                            {{ $abbr }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span style="font-size:.78rem; color:#9ca3af;">—</span>
                            @endif
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
                            @if($isEffectivelyEnded)
                                <span class="idx-chip chip-gray"><i class="fas fa-flag-checkered" style="font-size:.55rem;"></i> Ended</span>
                            @elseif($sub->status === 'active')
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
                        <td colspan="11" class="idx-empty">
                            <i class="fas fa-clipboard-list" style="font-size:2rem; color:#d1d5db;"></i><br>
                            No subscriptions found.
                            @if($search)
                                <br><span style="font-size:.8rem;">No results matching "<em>{{ $search }}</em>"</span>
                            @endif
                            @if(!$search && $statusFilter !== 'all')
                                <br><span style="font-size:.8rem;">Try switching the status filter to "All".</span>
                            @endif
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
