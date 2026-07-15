@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
.content-wrapper { background: #fff !important; }

.rpt-filter-bar {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    padding: 12px 0; margin-bottom: 16px;
}
.rpt-filter-bar input, .rpt-filter-bar select {
    padding: 7px 12px; border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: .83rem; color: #374151; outline: none; background: #fff;
}
.rpt-filter-bar input:focus, .rpt-filter-bar select:focus {
    border-color: #111827; box-shadow: 0 0 0 2px rgba(17,24,39,.07);
}
.rpt-search { position: relative; }
.rpt-search input { padding-left: 30px; width: 220px; }
.rpt-search .fa-search {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; font-size: .72rem; pointer-events: none;
}
.gw-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .72rem; font-weight: 600; padding: 2px 9px; border-radius: 20px;
}
.gw-cash    { background:#fef3c7; color:#92400e; }
.gw-hesabe  { background:#dbeafe; color:#1e40af; }
.gw-card    { background:#ede9fe; color:#5b21b6; }
.gw-default { background:#f3f4f6; color:#374151; }
.pay-pending {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .68rem; font-weight: 600; padding: 2px 8px; border-radius: 20px;
    background:#fee2e2; color:#dc2626; margin-left:4px;
}

.ref-cell {
    font-family: monospace; font-size: .75rem; color: #374151;
    max-width: 200px; word-break: break-all;
}
.ref-cell-short {
    font-family: monospace; font-size: .75rem; color: #6b7280;
    white-space: nowrap;
}

@media print {
    .app-sb, .main-header, .rpt-filter-bar, #printBtn,
    .content-header, nav, .pagination-wrap { display: none !important; }
    .content-wrapper { margin: 0 !important; padding: 8px !important; }
    .idx-card { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
    .rpt-print-header { display: block !important; }
}
.rpt-print-header { display: none; text-align: center; margin-bottom: 16px; font-size: 1rem; font-weight: 700; }
</style>

<div class="rpt-print-header">Subscription Sales Report</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('admin.reports.sales') }}" class="rpt-filter-bar">
    <div class="rpt-search">
        <i class="fas fa-search"></i>
        <input type="text" name="search" value="{{ $search }}" placeholder="Name, mobile, reference…">
    </div>

    <select name="gateway">
        <option value="">All Payments</option>
        @foreach($gateways as $gw)
            <option value="{{ $gw }}" {{ $gateway === $gw ? 'selected' : '' }}>{{ ucfirst($gw) }}</option>
        @endforeach
    </select>

    <input type="date" name="date_from" value="{{ $dateFrom }}" title="From date">
    <input type="date" name="date_to"   value="{{ $dateTo }}"   title="To date">

    <button type="submit" class="idx-btn ib-view" style="height:34px;">
        <i class="fas fa-filter"></i> Filter
    </button>
    @if($search || $gateway || $dateFrom || $dateTo)
    <a href="{{ route('admin.reports.sales') }}" class="idx-btn ib-gray" style="height:34px;">
        <i class="fas fa-times"></i> Clear
    </a>
    @endif

    <button id="printBtn" type="button" onclick="window.print()" class="idx-btn ib-gray" style="margin-left:auto;height:34px;">
        <i class="fas fa-file-pdf"></i> Download PDF
    </button>
</form>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-receipt mr-2" style="color:#0d9488;"></i> Subscription Sales</h3>
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:.8rem;color:#6b7280;">
                Total: <strong style="color:#15803d;">{{ number_format($totalAmount, 3) }} KWD</strong>
            </span>
            <span class="badge badge-secondary" style="font-size:.75rem;">{{ $sales->total() }} records</span>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:100px;">Date</th>
                        <th>Transaction ID</th>
                        <th style="width:80px;">Sub ID</th>
                        <th style="width:80px;">User ID</th>
                        <th style="width:110px;">Mobile</th>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th style="width:100px;">Start Date</th>
                        <th style="width:90px;">Payment</th>
                        <th style="width:90px; text-align:right;">Amount</th>
                        @if(auth()->user()->is_admin)
                        <th style="width:70px; text-align:center;">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sub)
                    @php
                        $gwClass = match($sub->payment_gateway) {
                            'cash'   => 'gw-cash',
                            'hesabe' => 'gw-hesabe',
                            'card'   => 'gw-card',
                            default  => 'gw-default',
                        };
                    @endphp
                    <tr>
                        <td style="white-space:nowrap; font-size:.8rem; color:#374151;">
                            {{ \Carbon\Carbon::parse($sub->getRawOriginal('created_at'))->format('d-m-Y') }}
                        </td>
                        <td>
                            @if($sub->payment_reference)
                                <span class="ref-cell" title="{{ $sub->payment_reference }}">
                                    {{ $sub->payment_reference }}
                                </span>
                            @else
                                <span style="color:#d1d5db; font-size:.78rem;">—</span>
                            @endif
                        </td>
                        <td style="font-weight:600; color:#111827;">#{{ $sub->id }}</td>
                        <td style="font-size:.82rem; color:#374151;">{{ $sub->user_id }}</td>
                        <td class="ref-cell-short">{{ $sub->user?->mobile ?? '—' }}</td>
                        <td style="font-weight:600; color:#111827;">{{ $sub->user?->name ?? '—' }}</td>
                        <td style="font-size:.82rem; color:#374151; max-width:180px;">
                            {{ $sub->subcrption_plans?->title ?? '—' }}
                        </td>
                        <td style="white-space:nowrap; font-size:.8rem; color:#374151;">
                            {{ \Carbon\Carbon::parse($sub->getRawOriginal('start_date'))->format('d-m-Y') }}
                        </td>
                        <td>
                            @if($sub->payment_gateway)
                                <span class="gw-badge {{ $gwClass }}">
                                    @if($sub->payment_gateway === 'cash')
                                        <i class="fas fa-money-bill-wave"></i>
                                    @elseif($sub->payment_gateway === 'hesabe')
                                        <i class="fas fa-credit-card"></i>
                                    @else
                                        <i class="fas fa-credit-card"></i>
                                    @endif
                                    {{ ucfirst($sub->payment_gateway) }}
                                </span>
                                @if($sub->payment !== 'paid')
                                    <span class="pay-pending"><i class="fas fa-clock"></i> Pending</span>
                                @endif
                            @else
                                <span style="color:#d1d5db; font-size:.78rem;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right; font-weight:700; color:#15803d; white-space:nowrap;">
                            {{ number_format($sub->price, 3) }} <span style="font-size:.7rem;color:#9ca3af;font-weight:400;">KWD</span>
                        </td>
                        @if(auth()->user()->is_admin)
                        <td style="text-align:center;">
                            <form action="{{ route('admin.user-subcrptions.destroy', $sub->id) }}" method="POST" class="rpt-del-form" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="button" class="idx-btn ib-del rpt-del-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->is_admin ? 11 : 10 }}" class="idx-empty">
                            <i class="fas fa-receipt" style="font-size:1.6rem;color:#e5e7eb;display:block;margin-bottom:8px;"></i>
                            No sales records found.
                            @if($search || $gateway || $dateFrom || $dateTo)
                                <br><span style="font-size:.8rem;">Try adjusting your filters.</span>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="pagination-wrap" style="padding:14px 20px; border-top:1px solid #f3f4f6;">
            {{ $sales->links('partials.pagination') }}
        </div>
        @endif
    </div>
</div>

@if(auth()->user()->is_admin)
@section('scripts')
<script>
document.querySelectorAll('.rpt-del-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var form = this.closest('.rpt-del-form');
        Swal.fire({
            title: 'Delete this sale record?',
            text: 'This will permanently delete the subscription record. This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        }).then(function (result) {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endsection
@endif

@endsection
