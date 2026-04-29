@extends('layouts.admin')

@section('styles')
<style>
/* ── Root variables ── */
:root {
    --db-bg:       #f0f2f5;
    --db-card:     #ffffff;
    --db-border:   #e2e8f0;
    --db-text:     #1e293b;
    --db-muted:    #64748b;
    --db-accent:   #3b82f6;
    --db-green:    #22c55e;
    --db-orange:   #f97316;
    --db-purple:   #a855f7;
    --db-red:      #ef4444;
    --db-shadow:   0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
    --db-radius:   14px;
}

/* ── Page wrapper ── */
.db-page {
    background: var(--db-bg);
    min-height: 100vh;
    padding: 28px 28px 40px;
}

/* ── Top bar ── */
.db-topbar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 32px;
}
.db-greeting h1 {
    font-size: 1.65rem;
    font-weight: 700;
    color: var(--db-text);
    margin: 0 0 4px;
}
.db-greeting p {
    font-size: 0.9rem;
    color: var(--db-muted);
    margin: 0;
}
.db-date-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--db-card);
    border: 1px solid var(--db-border);
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 0.875rem;
    color: var(--db-muted);
    box-shadow: var(--db-shadow);
}
.db-date-pill i { color: var(--db-accent); }

/* ── Generic card ── */
.db-card {
    background: var(--db-card);
    border: 1px solid var(--db-border);
    border-radius: var(--db-radius);
    box-shadow: var(--db-shadow);
}

/* ── KPI grid ── */
.db-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}
.db-kpi {
    padding: 24px;
    border-radius: var(--db-radius);
    display: flex;
    align-items: flex-start;
    gap: 18px;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.db-kpi:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
}
.db-kpi::before {
    content: '';
    position: absolute;
    bottom: -20px;
    right: -20px;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    opacity: 0.12;
    background: #fff;
}
.kpi-blue   { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.kpi-green  { background: linear-gradient(135deg, #22c55e, #16a34a); }
.kpi-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
.kpi-purple { background: linear-gradient(135deg, #a855f7, #9333ea); }

.db-kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: #fff;
    flex-shrink: 0;
}
.db-kpi-body { color: #fff; }
.db-kpi-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
}
.db-kpi-label {
    font-size: 0.85rem;
    opacity: 0.85;
    margin: 0;
}
.db-kpi-sub {
    font-size: 0.78rem;
    opacity: 0.7;
    margin-top: 2px;
}
.db-kpi-link {
    position: absolute;
    bottom: 14px;
    right: 16px;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
}
.db-kpi-link:hover { color: #fff; text-decoration: none; }

/* ── Secondary stat grid ── */
.db-sec-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}
.db-stat {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    border-radius: var(--db-radius);
    transition: transform 0.2s;
}
.db-stat:hover { transform: translateY(-2px); }
.db-stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.si-blue   { background: rgba(59,130,246,.12);  color: #3b82f6; }
.si-green  { background: rgba(34,197,94,.12);   color: #22c55e; }
.si-orange { background: rgba(249,115,22,.12);  color: #f97316; }
.si-purple { background: rgba(168,85,247,.12);  color: #a855f7; }
.si-red    { background: rgba(239,68,68,.12);   color: #ef4444; }
.si-teal   { background: rgba(20,184,166,.12);  color: #14b8a6; }
.si-indigo { background: rgba(99,102,241,.12);  color: #6366f1; }
.si-pink   { background: rgba(236,72,153,.12);  color: #ec4899; }

.db-stat-body { flex: 1; min-width: 0; }
.db-stat-value {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--db-text);
    line-height: 1;
    margin-bottom: 2px;
}
.db-stat-label {
    font-size: 0.8rem;
    color: var(--db-muted);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.db-stat-link {
    font-size: 0.8rem;
    color: var(--db-accent);
    text-decoration: none;
}
.db-stat-link:hover { text-decoration: underline; }

/* ── Middle two-column row ── */
.db-mid-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}
.db-panel-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--db-border);
}
.db-panel-header h6 {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--db-text);
    margin: 0;
    flex: 1;
}
.db-panel-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}
.db-panel-body { padding: 16px 20px; }

/* Payment status */
.db-pay-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.db-pay-row:last-child { margin-bottom: 0; }
.db-pay-info { display: flex; flex-direction: column; gap: 4px; }
.db-pay-label { font-size: 0.82rem; color: var(--db-muted); }
.db-pay-value { font-size: 1.1rem; font-weight: 700; color: var(--db-text); }
.db-bar-track {
    flex: 1;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    margin: 0 16px;
    overflow: hidden;
}
.db-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.8s ease;
}
.fill-green  { background: #22c55e; }
.fill-orange { background: #f97316; }
.db-pay-pct { font-size: 0.8rem; font-weight: 600; color: var(--db-muted); min-width: 36px; text-align: right; }

/* Today's activity */
.db-activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--db-border);
}
.db-activity-item:last-child { border-bottom: none; }
.db-activity-dot {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.db-activity-text { flex: 1; }
.db-activity-text strong { font-size: 0.88rem; color: var(--db-text); display: block; }
.db-activity-text span  { font-size: 0.78rem; color: var(--db-muted); }
.db-activity-count {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--db-text);
}

/* ── Recent subscriptions table ── */
.db-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--db-border);
}
.db-table-title {
    display: flex;
    align-items: center;
    gap: 10px;
}
.db-table-title h6 {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--db-text);
    margin: 0;
}
.db-table-title p {
    font-size: 0.78rem;
    color: var(--db-muted);
    margin: 0;
}
.db-btn-sm {
    font-size: 0.8rem;
    padding: 6px 14px;
    border-radius: 8px;
    background: var(--db-bg);
    border: 1px solid var(--db-border);
    color: var(--db-muted);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}
.db-btn-sm:hover { background: var(--db-border); color: var(--db-text); text-decoration: none; }

.db-table {
    width: 100%;
    border-collapse: collapse;
}
.db-table thead th {
    padding: 11px 16px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--db-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #f8fafc;
    border-bottom: 1px solid var(--db-border);
    text-align: left;
    white-space: nowrap;
}
.db-table tbody td {
    padding: 13px 16px;
    font-size: 0.875rem;
    color: var(--db-text);
    border-bottom: 1px solid var(--db-border);
    vertical-align: middle;
}
.db-table tbody tr:last-child td { border-bottom: none; }
.db-table tbody tr:hover td { background: #f8fafc; }

.db-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}
.pill-green  { background: rgba(34,197,94,.12);  color: #16a34a; }
.pill-orange { background: rgba(249,115,22,.12); color: #ea580c; }
.pill-gray   { background: rgba(100,116,139,.12); color: #64748b; }
.pill-blue   { background: rgba(59,130,246,.12);  color: #2563eb; }

.db-action-btn {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    background: var(--db-bg);
    border: 1px solid var(--db-border);
    color: var(--db-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.2s;
}
.db-action-btn:hover { background: var(--db-accent); border-color: var(--db-accent); color: #fff; text-decoration: none; }

/* Empty state */
.db-empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--db-muted);
}
.db-empty i { font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 12px; }
.db-empty p { margin: 0; font-size: 0.9rem; }

/* ── Responsive ── */
@media (max-width: 1200px) {
    .db-kpi-grid  { grid-template-columns: repeat(2, 1fr); }
    .db-sec-grid  { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .db-page       { padding: 16px 14px 32px; }
    .db-kpi-grid   { grid-template-columns: 1fr; gap: 14px; }
    .db-sec-grid   { grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .db-mid-row    { grid-template-columns: 1fr; }
    .db-kpi-value  { font-size: 1.6rem; }
    .db-table-header { flex-direction: column; align-items: flex-start; gap: 10px; }
}
@media (max-width: 480px) {
    .db-sec-grid { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content')
@php
    $paidPct    = $totalSubscriptions > 0 ? round($paidSubscriptions / $totalSubscriptions * 100) : 0;
    $pendingPct = $totalSubscriptions > 0 ? round($pendingSubscriptions / $totalSubscriptions * 100) : 0;
@endphp

<div class="db-page">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ── Top bar ── --}}
    <div class="db-topbar">
        <div class="db-greeting">
            <h1>Welcome back, {{ auth()->user()->name ?? 'Admin' }}</h1>
            <p>Here's what's happening with BalanceApp today.</p>
        </div>
        <div class="db-date-pill">
            <i class="fas fa-calendar-alt"></i>
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    {{-- ── Primary KPI cards ── --}}
    <div class="db-kpi-grid">
        <div class="db-card db-kpi kpi-blue">
            <div class="db-kpi-icon"><i class="fas fa-users"></i></div>
            <div class="db-kpi-body">
                <div class="db-kpi-value">{{ number_format($totalUsers) }}</div>
                <p class="db-kpi-label">Total Users</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="db-kpi-link">View <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="db-card db-kpi kpi-green">
            <div class="db-kpi-icon"><i class="fas fa-user-check"></i></div>
            <div class="db-kpi-body">
                <div class="db-kpi-value">{{ number_format($activeSubscriptions) }}</div>
                <p class="db-kpi-label">Active Subscriptions</p>
                <p class="db-kpi-sub">{{ number_format($totalSubscriptions) }} total</p>
            </div>
            <a href="{{ route('admin.user-subcrptions.index') }}" class="db-kpi-link">View <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="db-card db-kpi kpi-orange">
            <div class="db-kpi-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="db-kpi-body">
                <div class="db-kpi-value">{{ number_format($todaySubscriptions) }}</div>
                <p class="db-kpi-label">New Today</p>
                <p class="db-kpi-sub">{{ number_format($monthlySubscriptions) }} this month</p>
            </div>
        </div>

        <div class="db-card db-kpi kpi-purple">
            <div class="db-kpi-icon"><i class="fas fa-check-circle"></i></div>
            <div class="db-kpi-body">
                <div class="db-kpi-value">{{ number_format($paidSubscriptions) }}</div>
                <p class="db-kpi-label">Paid Subscriptions</p>
                <p class="db-kpi-sub">{{ number_format($pendingSubscriptions) }} pending</p>
            </div>
        </div>
    </div>

    {{-- ── Secondary stats row ── --}}
    <div class="db-sec-grid">
        <div class="db-card db-stat">
            <div class="db-stat-icon si-orange"><i class="fas fa-utensils"></i></div>
            <div class="db-stat-body">
                <div class="db-stat-value">{{ number_format($totalMeals) }}</div>
                <p class="db-stat-label">Meals</p>
            </div>
            <a href="{{ route('admin.meals.index') }}" class="db-stat-link"><i class="fas fa-external-link-alt"></i></a>
        </div>

        <div class="db-card db-stat">
            <div class="db-stat-icon si-blue"><i class="fas fa-list-alt"></i></div>
            <div class="db-stat-body">
                <div class="db-stat-value">{{ number_format($totalCategories) }}</div>
                <p class="db-stat-label">Categories</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="db-stat-link"><i class="fas fa-external-link-alt"></i></a>
        </div>

        <div class="db-card db-stat">
            <div class="db-stat-icon si-teal"><i class="fas fa-code-branch"></i></div>
            <div class="db-stat-body">
                <div class="db-stat-value">{{ number_format($totalBranches) }}</div>
                <p class="db-stat-label">Branches</p>
            </div>
            <a href="{{ route('admin.branches.index') }}" class="db-stat-link"><i class="fas fa-external-link-alt"></i></a>
        </div>

        <div class="db-card db-stat">
            <div class="db-stat-icon si-indigo"><i class="fas fa-map-marker-alt"></i></div>
            <div class="db-stat-body">
                <div class="db-stat-value">{{ number_format($totalAreas) }}</div>
                <p class="db-stat-label">Areas</p>
            </div>
            <a href="{{ route('admin.areas.index') }}" class="db-stat-link"><i class="fas fa-external-link-alt"></i></a>
        </div>

        <div class="db-card db-stat">
            <div class="db-stat-icon si-pink"><i class="fas fa-ticket-alt"></i></div>
            <div class="db-stat-body">
                <div class="db-stat-value">{{ number_format($totalCoupons) }}</div>
                <p class="db-stat-label">Coupons</p>
                <p class="db-stat-label" style="font-size:.72rem;">{{ $activeCoupons }} active</p>
            </div>
            <a href="{{ route('admin.coupons.index') }}" class="db-stat-link"><i class="fas fa-external-link-alt"></i></a>
        </div>

        <div class="db-card db-stat">
            <div class="db-stat-icon si-purple"><i class="fas fa-th-large"></i></div>
            <div class="db-stat-body">
                <div class="db-stat-value">{{ number_format($totalMealAssignments) }}</div>
                <p class="db-stat-label">Meal Assignments</p>
            </div>
        </div>

        <div class="db-card db-stat">
            <div class="db-stat-icon si-green"><i class="fas fa-chart-line"></i></div>
            <div class="db-stat-body">
                <div class="db-stat-value">{{ number_format($monthlySubscriptions) }}</div>
                <p class="db-stat-label">Monthly Subs</p>
            </div>
        </div>

        <div class="db-card db-stat">
            <div class="db-stat-icon si-orange"><i class="fas fa-calendar-day"></i></div>
            <div class="db-stat-body">
                <div class="db-stat-value">{{ number_format($todayMeals) }}</div>
                <p class="db-stat-label">Meals Added Today</p>
            </div>
        </div>
    </div>

    {{-- ── Middle row: Payment status + Today's activity ── --}}
    <div class="db-mid-row">

        {{-- Payment status --}}
        <div class="db-card">
            <div class="db-panel-header">
                <div class="db-panel-icon si-green"><i class="fas fa-credit-card"></i></div>
                <h6>Payment Status</h6>
            </div>
            <div class="db-panel-body">
                <div class="db-pay-row">
                    <div class="db-pay-info">
                        <span class="db-pay-label">Paid</span>
                        <span class="db-pay-value">{{ number_format($paidSubscriptions) }}</span>
                    </div>
                    <div class="db-bar-track">
                        <div class="db-bar-fill fill-green" style="width:{{ $paidPct }}%"></div>
                    </div>
                    <span class="db-pay-pct">{{ $paidPct }}%</span>
                </div>
                <div class="db-pay-row">
                    <div class="db-pay-info">
                        <span class="db-pay-label">Pending</span>
                        <span class="db-pay-value">{{ number_format($pendingSubscriptions) }}</span>
                    </div>
                    <div class="db-bar-track">
                        <div class="db-bar-fill fill-orange" style="width:{{ $pendingPct }}%"></div>
                    </div>
                    <span class="db-pay-pct">{{ $pendingPct }}%</span>
                </div>
                <p class="mt-3 mb-0" style="font-size:.8rem;color:var(--db-muted);">
                    {{ number_format($totalSubscriptions) }} total subscriptions recorded.
                </p>
            </div>
        </div>

        {{-- Today's activity --}}
        <div class="db-card">
            <div class="db-panel-header">
                <div class="db-panel-icon si-blue"><i class="fas fa-bolt"></i></div>
                <h6>Today's Activity</h6>
                <span style="font-size:.78rem;color:var(--db-muted);">{{ now()->format('M j, Y') }}</span>
            </div>
            <div class="db-panel-body">
                <div class="db-activity-item">
                    <div class="db-activity-dot si-green"><i class="fas fa-user-plus"></i></div>
                    <div class="db-activity-text">
                        <strong>New Subscriptions</strong>
                        <span>Signed up today</span>
                    </div>
                    <span class="db-activity-count">{{ $todaySubscriptions }}</span>
                </div>
                <div class="db-activity-item">
                    <div class="db-activity-dot si-orange"><i class="fas fa-utensils"></i></div>
                    <div class="db-activity-text">
                        <strong>Meals Added</strong>
                        <span>New items today</span>
                    </div>
                    <span class="db-activity-count">{{ $todayMeals }}</span>
                </div>
                <div class="db-activity-item">
                    <div class="db-activity-dot si-blue"><i class="fas fa-user-check"></i></div>
                    <div class="db-activity-text">
                        <strong>Active Subscriptions</strong>
                        <span>Currently running</span>
                    </div>
                    <span class="db-activity-count">{{ $activeSubscriptions }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Recent subscriptions table ── --}}
    <div class="db-card">
        <div class="db-table-header">
            <div class="db-table-title">
                <div class="db-panel-icon si-blue"><i class="fas fa-history"></i></div>
                <div>
                    <h6>Recent Subscriptions</h6>
                    <p>Latest subscription activity</p>
                </div>
            </div>
            <a href="{{ route('admin.user-subcrptions.index') }}" class="db-btn-sm">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        @if($recentSubscriptions->count() > 0)
            <div class="table-responsive">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Start</th>
                            <th>End</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSubscriptions as $sub)
                        <tr>
                            <td style="color:var(--db-muted);font-weight:500;">#{{ $sub->id }}</td>
                            <td>{{ $sub->user->name ?? '—' }}</td>
                            <td>{{ $sub->subcrption_plans->title ?? '—' }}</td>
                            <td>
                                @if($sub->status === 'active')
                                    <span class="db-pill pill-green">Active</span>
                                @else
                                    <span class="db-pill pill-gray">{{ ucfirst($sub->status ?? 'Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($sub->payment === 'paid')
                                    <span class="db-pill pill-blue">Paid</span>
                                @else
                                    <span class="db-pill pill-orange">Pending</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">{{ $sub->start_date ?? '—' }}</td>
                            <td style="white-space:nowrap;">{{ $sub->end_date ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.user-subcrptions.show', $sub->id) }}" class="db-action-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="db-empty">
                <i class="fas fa-inbox"></i>
                <p>No subscriptions yet.</p>
            </div>
        @endif
    </div>

</div>
@endsection
