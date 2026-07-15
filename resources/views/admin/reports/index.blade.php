@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
.content-wrapper { background: #fff !important; }

/* KPI cards */
.rpt-kpi-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 24px; }
@media(max-width:900px){ .rpt-kpi-row { grid-template-columns: repeat(2,1fr); } }
@media(max-width:520px){ .rpt-kpi-row { grid-template-columns: 1fr; } }

.rpt-kpi {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 18px 20px; display: flex; align-items: center; gap: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.rpt-kpi-ic {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.rpt-kpi-val { font-size: 1.3rem; font-weight: 700; color: #111827; line-height: 1.2; }
.rpt-kpi-lbl { font-size: .75rem; color: #6b7280; margin-top: 2px; }

/* Filter bar */
.rpt-filter { display:flex; align-items:center; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
.rpt-filter select {
    padding: 7px 12px; border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: .85rem; color: #374151; outline: none;
}
.rpt-filter select:focus { border-color: #111827; }

/* Section title */
.rpt-section-title {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #9ca3af; margin: 0 0 10px;
}

/* Currency */
.rpt-currency { font-size: .72rem; color: #9ca3af; font-weight: 400; margin-left:2px; }

/* Month name */
.month-names { display:none; }

/* Print styles */
@media print {
    .app-sb, .main-header, .rpt-filter, #printBtn, .content-header, nav { display: none !important; }
    .content-wrapper { margin: 0 !important; padding: 0 !important; background: #fff !important; }
    .rpt-kpi-row { grid-template-columns: repeat(4,1fr) !important; }
    .idx-card { box-shadow: none !important; border: 1px solid #e5e7eb !important; break-inside: avoid; }
    body { font-size: 12px; }
    .rpt-print-header { display: block !important; }
}
.rpt-print-header {
    display: none;
    text-align: center; margin-bottom: 20px;
    font-size: 1.1rem; font-weight: 700; color: #111827;
}
</style>

@php
$monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
$currency   = 'KWD';
@endphp

<div class="rpt-print-header">
    Income Report — {{ $year }}
</div>

{{-- Filter + Download --}}
<div class="rpt-filter">
    <form method="GET" action="{{ route('admin.reports.index') }}" style="display:flex;gap:8px;align-items:center;">
        <label style="font-size:.83rem;font-weight:600;color:#374151;">Year</label>
        <select name="year" onchange="this.form.submit()">
            @foreach($years as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </form>
    <button id="printBtn" onclick="window.print()" class="idx-btn ib-gray" style="margin-left:auto;">
        <i class="fas fa-file-pdf"></i> Download PDF
    </button>
</div>

{{-- KPI cards --}}
<div class="rpt-kpi-row" style="grid-template-columns:repeat(5,1fr);">
    <div class="rpt-kpi">
        <div class="rpt-kpi-ic" style="background:#dcfce7; color:#15803d;"><i class="fas fa-coins"></i></div>
        <div>
            <div class="rpt-kpi-val">{{ number_format($totals['income'], 3) }} <span class="rpt-currency">{{ $currency }}</span></div>
            <div class="rpt-kpi-lbl">Collected Revenue {{ $year }}</div>
        </div>
    </div>
    <div class="rpt-kpi">
        <div class="rpt-kpi-ic" style="background:#fef3c7; color:#b45309;"><i class="fas fa-money-bill-wave"></i></div>
        <div>
            <div class="rpt-kpi-val">{{ number_format($totals['cash'], 3) }} <span class="rpt-currency">{{ $currency }}</span></div>
            <div class="rpt-kpi-lbl">Cash on Delivery</div>
        </div>
    </div>
    <div class="rpt-kpi">
        <div class="rpt-kpi-ic" style="background:#dbeafe; color:#1d4ed8;"><i class="fas fa-credit-card"></i></div>
        <div>
            <div class="rpt-kpi-val">{{ number_format($totals['card'], 3) }} <span class="rpt-currency">{{ $currency }}</span></div>
            <div class="rpt-kpi-lbl">Card / Online</div>
        </div>
    </div>
    <div class="rpt-kpi">
        <div class="rpt-kpi-ic" style="background:#ede9fe; color:#5b21b6;"><i class="fas fa-clipboard-check"></i></div>
        <div>
            <div class="rpt-kpi-val">{{ $totals['count'] }}</div>
            <div class="rpt-kpi-lbl">Total Subscriptions</div>
        </div>
    </div>
    <div class="rpt-kpi">
        <div class="rpt-kpi-ic" style="background:#fee2e2; color:#dc2626;"><i class="fas fa-clock"></i></div>
        <div>
            <div class="rpt-kpi-val">{{ $totals['pending_count'] }}</div>
            <div class="rpt-kpi-lbl">Pending · {{ number_format($totals['pending_income'], 3) }} <span class="rpt-currency">{{ $currency }}</span></div>
        </div>
    </div>
</div>

{{-- Monthly breakdown --}}
<div class="card idx-card mb-4">
    <div class="card-header">
        <h3><i class="fas fa-chart-bar mr-2" style="color:#16a34a;"></i> Monthly Breakdown — {{ $year }}</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:right;">Cash ({{ $currency }})</th>
                        <th style="text-align:right;">Card / Online ({{ $currency }})</th>
                        <th style="text-align:right;">Pending</th>
                        <th style="text-align:right;">Collected ({{ $currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandTotal = 0; $grandCash = 0; $grandCard = 0; $grandCount = 0;
                        $grandPendingCount = 0; $grandPendingIncome = 0;
                    @endphp
                    @for($m = 1; $m <= 12; $m++)
                    @php
                        $row            = $monthly->get($m);
                        $income         = $row?->total_income   ?? 0;
                        $cash           = $row?->cash_income    ?? 0;
                        $card           = $row?->card_income    ?? 0;
                        $count          = $row?->total_count    ?? 0;
                        $pendingCount   = $row?->pending_count  ?? 0;
                        $pendingIncome  = $row?->pending_income ?? 0;
                        $grandTotal        += $income;
                        $grandCash         += $cash;
                        $grandCard         += $card;
                        $grandCount        += $count;
                        $grandPendingCount  += $pendingCount;
                        $grandPendingIncome += $pendingIncome;
                    @endphp
                    <tr style="{{ !$row ? 'color:#d1d5db;' : '' }}">
                        <td style="font-weight:600;">{{ $monthNames[$m-1] }}</td>
                        <td style="text-align:right;">
                            @if($row)
                                <span class="idx-chip chip-violet">{{ $count }}</span>
                            @else —
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if($row && $cash > 0)
                                <span class="idx-chip chip-yellow">{{ number_format($cash, 3) }}</span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if($row && $card > 0)
                                <span class="idx-chip chip-blue">{{ number_format($card, 3) }}</span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if($row && $pendingCount > 0)
                                <span class="idx-chip chip-red" style="font-size:.7rem;">{{ $pendingCount }} · {{ number_format($pendingIncome, 3) }}</span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if($row)
                                <strong style="color:#111827;">{{ number_format($income, 3) }}</strong>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr style="background:#f9fafb; font-weight:700; border-top:2px solid #e5e7eb;">
                        <td style="font-weight:700; color:#111827;">Total</td>
                        <td style="text-align:right; font-weight:700;">{{ $grandCount }}</td>
                        <td style="text-align:right; font-weight:700; color:#b45309;">{{ number_format($grandCash, 3) }}</td>
                        <td style="text-align:right; font-weight:700; color:#1d4ed8;">{{ number_format($grandCard, 3) }}</td>
                        <td style="text-align:right; font-weight:700; color:#dc2626;">{{ $grandPendingCount }} · {{ number_format($grandPendingIncome, 3) }}</td>
                        <td style="text-align:right; font-weight:700; color:#15803d; font-size:1rem;">{{ number_format($grandTotal, 3) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Per-plan breakdown --}}
<div class="card idx-card mb-4">
    <div class="card-header">
        <h3><i class="fas fa-layer-group mr-2" style="color:#5b21b6;"></i> Income by Plan — {{ $year }}</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:right;">Cash ({{ $currency }})</th>
                        <th style="text-align:right;">Card / Online ({{ $currency }})</th>
                        <th style="text-align:right;">Pending</th>
                        <th style="text-align:right;">Collected ({{ $currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byPlan as $row)
                    <tr>
                        <td><span class="idx-chip chip-violet">{{ $row->subcrption_plans->title ?? '—' }}</span></td>
                        <td style="text-align:right; font-weight:600;">{{ $row->total_count }}</td>
                        <td style="text-align:right;">
                            @if($row->cash_income > 0)
                                <span style="color:#b45309; font-weight:600;">{{ number_format($row->cash_income, 3) }}</span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if($row->card_income > 0)
                                <span style="color:#1d4ed8; font-weight:600;">{{ number_format($row->card_income, 3) }}</span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if(($row->pending_count ?? 0) > 0)
                                <span style="color:#dc2626; font-size:.8rem;">{{ $row->pending_count }} · {{ number_format($row->pending_income, 3) }}</span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right; font-weight:700; color:#111827;">{{ number_format($row->total_income, 3) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="idx-empty">
                            <i class="fas fa-chart-bar" style="font-size:1.6rem;color:#e5e7eb;display:block;margin-bottom:8px;"></i>
                            No paid subscriptions in {{ $year }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
