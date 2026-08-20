@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
.meal-thumb {
    width: 38px; height: 38px; border-radius: 7px; object-fit: cover;
    flex-shrink: 0; border: 1px solid #e5e7eb;
}
.meal-item {
    display: flex; align-items: center; gap: 9px; margin-bottom: 6px;
}
.meal-item:last-child { margin-bottom: 0; }
.meal-name { font-size: .83rem; font-weight: 600; line-height: 1.3; color: #111827; }
.meal-sub  { font-size: .72rem; color: #9ca3af; }
.no-items  { color: #d1d5db; font-size: .78rem; font-style: italic; }
.meal-extras { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 3px; }
.extra-chip {
    font-size: .66rem; color: #0f766e; background: #f0fdfa; border: 1px solid #99f6e4;
    padding: 1px 7px; border-radius: 20px; display: inline-flex; gap: 3px; line-height: 1.5;
}
.extra-chip strong { font-weight: 700; }

.slot-badge {
    display: inline-block; background: #f3f4f6; color: #374151;
    border-radius: 20px; padding: 3px 11px;
    font-size: .75rem; font-weight: 600; white-space: nowrap;
}

.status-select {
    -webkit-appearance: none; appearance: none;
    border-radius: 20px; padding: 4px 28px 4px 12px;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid; width: 100%;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23666'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 9px center;
    outline: none; transition: all .15s;
}
.status-pending   { border-color: #fbbf24; color: #92400e; background-color: #fef3c7; }
.status-delivered { border-color: #34d399; color: #065f46; background-color: #d1fae5; }

/* Locked (delivered) status — read-only pill */
.status-locked {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; border-radius: 20px; padding: 4px 12px;
    font-size: .78rem; font-weight: 700; cursor: not-allowed;
    border: 1.5px solid #34d399; color: #065f46; background-color: #d1fae5;
}
.status-locked .fa-lock { font-size: .66rem; opacity: .8; }

.del-filter-bar {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    padding: 10px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
}
.del-search { position: relative; }
.del-search input {
    padding: 5px 12px 5px 30px; border-radius: 20px;
    border: 1px solid #e5e7eb; font-size: .82rem; height: 32px; width: 200px; outline: none;
}
.del-search input:focus { border-color: #111827; box-shadow: 0 0 0 2px rgba(17,24,39,.08); }
.del-search .fa-search { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: .72rem; }

.diet-chip {
    display: inline-block; font-size: .68rem; font-weight: 600; padding: 1px 8px;
    border-radius: 20px; margin: 1px 2px 1px 0;
}
.diet-personalized { background: #dbeafe; color: #1e40af; }
.diet-label { font-size: .74rem; color: #374151; margin-top: 3px; line-height: 1.4; }
.diet-label strong { color: #111827; font-weight: 700; }
.diet-note { font-size: .72rem; color: #6b7280; }
.diet-note i { color: #9ca3af; margin-right: 3px; }
.diet-none { font-size: .78rem; color: #d1d5db; }
</style>

@php
$formatSlot = fn($slot) => $slotLabels[$slot] ?? ($slot ? ucwords(str_replace('_', ' ', $slot)) : '—');
@endphp

<div class="card idx-card">
    <div class="card-header">
        <h3>
            <i class="fas fa-truck mr-2" style="color:#0d9488;"></i>
            @if($date->isToday() && !request()->has('date'))
                Today's Delivery Orders
            @else
                Delivery Orders : {{ $date->format('l, d M Y') }}
                @if($date->isToday())
                    <span style="font-size:.8rem; font-weight:400; color:#9ca3af;">(Today)</span>
                @endif
            @endif
            <span class="badge badge-secondary ml-2" style="font-size:.75rem;">{{ $deliveryOrders->total() }}</span>
        </h3>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <a href="{{ route('admin.delivery-orders.print-all', ['date' => $date->format('Y-m-d')]) }}"
               target="_blank" class="idx-btn ib-gray">
                <i class="fas fa-print"></i> Print All
            </a>
            <button id="makeAllDeliveredBtn" class="idx-btn ib-green">
                <i class="fas fa-check-double"></i> Make All Delivered
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.delivery-orders.index') }}" class="del-filter-bar">
        <div class="del-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search customer...">
        </div>
        <div class="input-group input-group-sm" style="width:175px;">
            <div class="input-group-prepend">
                <span class="input-group-text" style="padding:0 8px;">
                    <i class="fas fa-calendar-alt" style="font-size:.72rem;"></i>
                </span>
            </div>
            <input type="date" name="date" class="form-control form-control-sm"
                   autocomplete="off" value="{{ $date->format('Y-m-d') }}">
        </div>
        <button type="submit" class="idx-btn ib-view" style="height:32px;">
            <i class="fas fa-filter"></i> Apply
        </button>
        @if(!$date->isToday() || $search)
        <a href="{{ route('admin.delivery-orders.index') }}" class="idx-btn ib-gray" style="height:32px;">
            Clear
        </a>
        @endif
        <div style="margin-left:auto; display:flex; align-items:center; gap:12px; font-size:.76rem; color:#6b7280;">
            <span><i class="fas fa-circle" style="color:#fbbf24; font-size:.5rem;"></i> Pending</span>
            <span><i class="fas fa-circle" style="color:#34d399; font-size:.5rem;"></i> Delivered</span>
        </div>
    </form>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:16%;">Customer</th>
                        <th style="width:120px;">Delivery Time</th>
                        <th>Meals</th>
                        <th>Snacks</th>
                        <th style="width:14%;">Diet</th>
                        <th style="width:14%;">Delivery Notes</th>
                        <th style="width:148px;">Status</th>
                        <th style="width:64px; text-align:center;">Print</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveryOrders as $order)
                    @php
                        $sub    = $order->subscription;
                        $addr   = $sub->address;
                        $day    = $order->subscriptionDay;
                        $meals  = $day->subscription_meals->where('type', 'is meal');
                        $snacks = $day->subscription_meals->where('type', 'is snack');
                    @endphp
                    <tr data-order-id="{{ $order->id }}">
                        <td>
                            <div style="font-weight:600; color:#111827;">{{ $sub->user->name ?? '—' }}</div>
                            <div style="font-size:.75rem; color:#9ca3af; margin-top:2px;">
                                <i class="fas fa-phone" style="font-size:.62rem;"></i>
                                {{ $addr?->phone_number ?? $sub->user->mobile ?? '—' }}
                            </div>
                        </td>
                        <td>
                            <span class="slot-badge">
                                {{ $formatSlot($addr?->preferred_delivery_slot ?? '') }}
                            </span>
                        </td>
                        <td>
                            @forelse($meals as $sm)
                                <div class="meal-item">
                                    @if($sm->meal?->image)
                                        <img src="{{ $sm->meal->image->getUrl('thumb') }}" class="meal-thumb" alt="">
                                    @else
                                        <img src="/images/placeholder.png" class="meal-thumb" alt="">
                                    @endif
                                    <div>
                                        <div class="meal-name">{{ $sm->meal->title ?? '—' }}</div>
                                        @if($sm->meal?->extras)
                                            <div class="meal-sub">{{ $sm->meal->extras }}</div>
                                        @endif
                                        @include('admin.deliveries._extras', ['sm' => $sm])
                                    </div>
                                </div>
                            @empty
                                <span class="no-items">No meals</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse($snacks as $sm)
                                <div class="meal-item">
                                    @if($sm->meal?->image)
                                        <img src="{{ $sm->meal->image->getUrl('thumb') }}" class="meal-thumb" alt="">
                                    @else
                                        <img src="/images/placeholder.png" class="meal-thumb" alt="">
                                    @endif
                                    <div>
                                        <div class="meal-name">{{ $sm->meal->title ?? '—' }}</div>
                                        @include('admin.deliveries._extras', ['sm' => $sm])
                                    </div>
                                </div>
                            @empty
                                <span class="no-items">No snacks</span>
                            @endforelse
                        </td>
                        <td>
                            @php $user = $sub->user; @endphp
                            @if($sub->is_personalized)
                                <span class="diet-chip diet-personalized">
                                    <i class="fas fa-dumbbell" style="font-size:.6rem;"></i>
                                    Protein: {{ $sub->protein ?? '—' }}g · Carbs: {{ $sub->carbs ?? '—' }}g
                                </span>
                            @endif
                            @if($user && $user->has_food_allergies && !empty($user->allergies))
                                <div class="diet-label"><strong>Allergies:</strong> {{ implode(', ', (array) $user->allergies) }}</div>
                            @endif
                            @if($user && !empty($user->dislikes))
                                <div class="diet-label"><strong>Dislike:</strong> {{ implode(', ', (array) $user->dislikes) }}</div>
                            @endif
                            @if(!$sub->is_personalized && (!$user || !$user->has_food_allergies || empty($user->allergies)) && (!$user || empty($user->dislikes)))
                                <span class="diet-none">—</span>
                            @endif
                        </td>
                        <td>
                            @if($addr?->delivery_notes)
                                <div class="diet-note"><i class="fas fa-sticky-note"></i> {{ $addr->delivery_notes }}</div>
                            @else
                                <span class="diet-none">—</span>
                            @endif
                        </td>
                        <td>
                            @if($order->status === 'delivered')
                                <span class="status-locked" title="Delivered — locked">
                                    <i class="fas fa-lock"></i> Delivered
                                </span>
                            @else
                                <select class="status-select status-{{ $order->status }}"
                                        data-order-id="{{ $order->id }}">
                                    <option value="pending"   {{ $order->status === 'pending'   ? 'selected' : '' }}>Pending</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                </select>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.delivery-orders.print', $order->id) }}"
                               target="_blank" class="idx-btn ib-gray" title="Print Delivery Note"
                               style="padding:4px 9px;">
                                <i class="fas fa-file-alt"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="idx-empty">
                            <i class="fas fa-box-open" style="font-size:1.6rem; color:#e5e7eb; display:block; margin-bottom:8px;"></i>
                            No delivery orders scheduled for <strong>{{ $date->format('l, d M Y') }}</strong>.
                            @if($search)
                                <br><span style="font-size:.8rem;">No results matching "<em>{{ $search }}</em>"</span>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveryOrders->hasPages())
        <div style="padding: 14px 20px; border-top: 1px solid #f3f4f6;">
            {{ $deliveryOrders->links('partials.pagination') }}
        </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(function () {
    var csrfToken     = '{{ csrf_token() }}';
    var deliveryDate  = @json($date->format('Y-m-d'));
    var deliveryLabel = @json($date->format('d M Y'));

    $(document).on('change', '.status-select', function () {
        var $sel    = $(this);
        var orderId = $sel.data('order-id');
        var status  = $sel.val();
        $sel.removeClass('status-pending status-delivered').addClass('status-' + status);
        $.post('/admin/delivery-orders/' + orderId + '/status', {
            _token: csrfToken, status: status
        }).done(function () {
            // Once delivered, lock the row so it can't be changed back.
            if (status === 'delivered') {
                $sel.replaceWith(
                    '<span class="status-locked" title="Delivered — locked">' +
                    '<i class="fas fa-lock"></i> Delivered</span>'
                );
            }
        }).fail(function () { alert('Failed to update status.'); });
    });

    $('#makeAllDeliveredBtn').on('click', function () {
        if (!confirm('Mark all orders as Delivered for ' + deliveryLabel + '?')) return;
        $.post('{{ route("admin.delivery-orders.make-all-delivered") }}', {
            _token: csrfToken, date: deliveryDate
        }).done(function () {
            // Reload so every newly-delivered order renders in its locked state.
            location.reload();
        }).fail(function () { alert('Failed to update.'); });
    });
});
</script>
@endsection
