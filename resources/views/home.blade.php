@extends('layouts.admin')

@section('styles')
<style>
/* ── Force white bg for content wrapper ── */
.content-wrapper { background: #ffffff !important; }
.main-footer      { background: #ffffff !important; border-top: 1px solid #e5e7eb !important; }

/* ── Variables ── */
:root {
    --c-text:    #111827;
    --c-sub:     #6b7280;
    --c-border:  #e5e7eb;
    --c-bg:      #f9fafb;
    --c-white:   #ffffff;
    --c-radius:  12px;
    --c-shadow:  0 1px 3px rgba(0,0,0,.07), 0 2px 8px rgba(0,0,0,.05);
}

/* ── Page ── */
.db { padding: 28px 28px 48px; background: #ffffff; }

/* ── Topbar ── */
.db-topbar {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 30px;
}
.db-topbar h2 { font-size: 1.45rem; font-weight: 700; color: var(--c-text); margin: 0 0 3px; }
.db-topbar p  { font-size: .86rem; color: var(--c-sub); margin: 0; }
.db-date {
    display: flex; align-items: center; gap: 7px;
    background: var(--c-bg); border: 1px solid var(--c-border);
    border-radius: 20px; padding: 6px 14px;
    font-size: .82rem; color: var(--c-sub);
}
.db-date i { color: #16a34a; }

/* ── Generic card ── */
.db-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--c-radius);
    box-shadow: var(--c-shadow);
}

/* ── Section title ── */
.db-sec-title {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: #9ca3af; margin: 0 0 14px;
}

/* ══════════════════════
   KPI cards
══════════════════════ */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}
.kpi {
    padding: 22px 20px 18px;
    border-radius: var(--c-radius);
    border: 1px solid var(--c-border);
    box-shadow: var(--c-shadow);
    background: var(--c-white);
    position: relative;
    overflow: hidden;
    transition: transform .18s, box-shadow .18s;
    text-decoration: none !important;
    display: block;
}
.kpi:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }

/* coloured top stripe */
.kpi::before {
    content: ''; position: absolute; inset: 0 0 auto 0;
    height: 3px; border-radius: var(--c-radius) var(--c-radius) 0 0;
}
.kpi-blue::before   { background: #3b82f6; }
.kpi-green::before  { background: #22c55e; }
.kpi-teal::before   { background: #14b8a6; }
.kpi-purple::before { background: #a855f7; }

.kpi-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.kpi-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; flex-shrink: 0;
}
.ki-blue   { background: #eff6ff; color: #3b82f6; }
.ki-green  { background: #f0fdf4; color: #16a34a; }
.ki-teal   { background: #f0fdfa; color: #0d9488; }
.ki-purple { background: #faf5ff; color: #9333ea; }

.kpi-badge {
    font-size: .7rem; font-weight: 600; padding: 3px 9px;
    border-radius: 20px; background: var(--c-bg); color: var(--c-sub);
    border: 1px solid var(--c-border);
}
.kpi-value { font-size: 2rem; font-weight: 800; color: var(--c-text); line-height: 1; margin-bottom: 4px; }
.kpi-label { font-size: .82rem; color: var(--c-sub); margin: 0; }
.kpi-link  {
    font-size: .75rem; color: #9ca3af; text-decoration: none !important;
    display: inline-flex; align-items: center; gap: 4px; margin-top: 10px;
    transition: color .15s;
}
.kpi:hover .kpi-link { color: #374151; }

/* ══════════════════════
   Stat mini-cards
══════════════════════ */
.stat-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.stat {
    padding: 16px;
    display: flex; align-items: center; gap: 13px;
    border-radius: 10px;
    border: 1px solid var(--c-border);
    background: var(--c-white);
    box-shadow: var(--c-shadow);
    transition: transform .15s;
    text-decoration: none !important;
}
.stat:hover { transform: translateY(-2px); }
.stat-icon {
    width: 38px; height: 38px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: .88rem; flex-shrink: 0;
}
.si-orange { background: #fff7ed; color: #ea580c; }
.si-blue   { background: #eff6ff; color: #2563eb; }
.si-indigo { background: #eef2ff; color: #4338ca; }
.si-pink   { background: #fdf2f8; color: #db2777; }
.si-slate  { background: #f8fafc; color: #475569; }

.stat-val   { font-size: 1.3rem; font-weight: 700; color: var(--c-text); line-height: 1; }
.stat-label { font-size: .75rem; color: var(--c-sub); margin-top: 2px; white-space: nowrap; }

/* ══════════════════════
   Middle two-col
══════════════════════ */
.mid-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 24px;
}
.panel-hdr {
    display: flex; align-items: center; gap: 10px;
    padding: 16px 18px 14px;
    border-bottom: 1px solid var(--c-border);
}
.panel-hdr h6 { font-size: .92rem; font-weight: 600; color: var(--c-text); margin: 0; flex: 1; }
.panel-hdr-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; font-size: .82rem;
}
.panel-body { padding: 16px 18px; }

/* Activity list */
.activity-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 0; border-bottom: 1px solid #f3f4f6;
}
.activity-item:last-child { border-bottom: none; padding-bottom: 0; }
.act-dot {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem; flex-shrink: 0;
}
.act-body { flex: 1; }
.act-body strong { font-size: .85rem; color: var(--c-text); display: block; }
.act-body span   { font-size: .75rem; color: var(--c-sub); }
.act-count { font-size: 1.3rem; font-weight: 700; color: var(--c-text); }

/* Delivery donut-like summary */
.del-row {
    display: flex; gap: 10px;
}
.del-box {
    flex: 1; border-radius: 10px; padding: 16px 14px; text-align: center;
    border: 1px solid var(--c-border);
}
.del-box-val   { font-size: 1.8rem; font-weight: 800; line-height: 1; margin-bottom: 4px; }
.del-box-label { font-size: .75rem; color: var(--c-sub); }
.del-total  { background: #f9fafb; }
.del-done   { background: #f0fdf4; border-color: #bbf7d0; }
.del-done   .del-box-val { color: #16a34a; }
.del-pend   { background: #fffbeb; border-color: #fde68a; }
.del-pend   .del-box-val { color: #d97706; }

.del-link {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    margin-top: 14px; padding: 9px 14px;
    border-radius: 8px; background: var(--c-bg); border: 1px solid var(--c-border);
    font-size: .82rem; color: var(--c-sub); text-decoration: none !important;
    transition: all .15s;
}
.del-link:hover { background: #111827; color: #fff; border-color: #111827; }

/* ══════════════════════
   Recent table
══════════════════════ */
.tbl-hdr {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 18px 14px; border-bottom: 1px solid var(--c-border);
    flex-wrap: wrap; gap: 8px;
}
.tbl-hdr h6 { font-size: .92rem; font-weight: 600; color: var(--c-text); margin: 0; }
.tbl-hdr p  { font-size: .75rem; color: var(--c-sub); margin: 2px 0 0; }
.view-all {
    font-size: .8rem; padding: 6px 14px;
    border: 1px solid var(--c-border); border-radius: 8px;
    color: var(--c-sub); text-decoration: none !important; background: var(--c-bg);
    display: inline-flex; align-items: center; gap: 5px; transition: all .15s;
}
.view-all:hover { background: var(--c-text); color: #fff; border-color: var(--c-text); }

.db-tbl { width: 100%; border-collapse: collapse; }
.db-tbl thead th {
    padding: 10px 16px; font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    color: var(--c-sub); background: var(--c-bg);
    border-bottom: 1px solid var(--c-border); text-align: left; white-space: nowrap;
}
.db-tbl tbody td {
    padding: 12px 16px; font-size: .86rem; color: var(--c-text);
    border-bottom: 1px solid #f3f4f6; vertical-align: middle;
}
.db-tbl tbody tr:last-child td { border-bottom: none; }
.db-tbl tbody tr:hover td { background: #f9fafb; }

.pill {
    display: inline-block; padding: 3px 10px; border-radius: 20px;
    font-size: .72rem; font-weight: 600;
}
.pill-green  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.pill-gray   { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.pill-blue   { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }

.act-btn {
    width: 28px; height: 28px; border-radius: 7px;
    background: var(--c-bg); border: 1px solid var(--c-border); color: var(--c-sub);
    display: inline-flex; align-items: center; justify-content: center;
    text-decoration: none !important; font-size: .75rem; transition: all .15s;
}
.act-btn:hover { background: #111827; border-color: #111827; color: #fff; }

.db-empty { padding: 48px; text-align: center; color: var(--c-sub); }
.db-empty i { font-size: 2rem; opacity: .25; display: block; margin-bottom: 10px; }

/* ── Branch dashboard ── */
.branch-kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}
.branch-del-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 24px;
}
.branch-stat-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}

/* ── Responsive ── */
@media (max-width: 1200px) { .kpi-row { grid-template-columns: repeat(2,1fr); } .stat-row { grid-template-columns: repeat(3,1fr); } .branch-kpi-row { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 900px)  { .stat-row { grid-template-columns: repeat(2,1fr); } .mid-row { grid-template-columns: 1fr; } .branch-del-grid { grid-template-columns: 1fr; } .branch-stat-row { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 640px)  { .db { padding: 16px 14px 32px; } .kpi-row { grid-template-columns: 1fr; } .stat-row { grid-template-columns: repeat(2,1fr); } .branch-kpi-row { grid-template-columns: 1fr; } .branch-stat-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="db">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- ── Topbar ── --}}
    <div class="db-topbar">
        <div>
            @if($isBranchUser)
            <h2>{{ $branchName }} Branch</h2>
            <p>Today's operations overview for your branch.</p>
            @else
            <h2>Welcome back, {{ auth()->user()->name ?? 'Admin' }}</h2>
            <p>Here's an overview of Balance operations for today.</p>
            @endif
        </div>
        <div class="db-date">
            <i class="fas fa-calendar-alt"></i>
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    @if($isBranchUser)
    {{-- ══════════════════════════════════════════
         BRANCH DASHBOARD
    ══════════════════════════════════════════ --}}

    {{-- Branch KPI cards --}}
    <p class="db-sec-title">Today's Overview</p>
    <div class="branch-kpi-row">

        <div class="kpi kpi-teal" style="cursor:default;">
            <div class="kpi-top">
                <div class="kpi-icon ki-teal"><i class="fas fa-truck"></i></div>
                <span class="kpi-badge">Today</span>
            </div>
            <div class="kpi-value">{{ number_format($branchDeliveryTotal) }}</div>
            <p class="kpi-label">Scheduled Deliveries</p>
        </div>

        <a href="{{ route('admin.delivery-orders.index') }}" class="kpi kpi-green">
            <div class="kpi-top">
                <div class="kpi-icon ki-green"><i class="fas fa-check-circle"></i></div>
                <span class="kpi-badge">Done</span>
            </div>
            <div class="kpi-value">{{ number_format($branchDelivered) }}</div>
            <p class="kpi-label">Delivered Today</p>
            <span class="kpi-link">View orders <i class="fas fa-arrow-right"></i></span>
        </a>

        <div class="kpi kpi-purple" style="cursor:default;">
            <div class="kpi-top">
                <div class="kpi-icon ki-purple"><i class="fas fa-hourglass-half"></i></div>
                <span class="kpi-badge">Pending</span>
            </div>
            <div class="kpi-value">{{ number_format($branchPending) }}</div>
            <p class="kpi-label">Pending Deliveries</p>
        </div>

        <a href="{{ route('admin.user-subcrptions.index') }}" class="kpi kpi-blue">
            <div class="kpi-top">
                <div class="kpi-icon ki-blue"><i class="fas fa-clipboard-check"></i></div>
                <span class="kpi-badge">Active</span>
            </div>
            <div class="kpi-value">{{ number_format($branchActiveSubscriptions) }}</div>
            <p class="kpi-label">Active Subscriptions <span style="font-size:.75rem;color:#9ca3af;">/ {{ number_format($branchTotalSubscriptions) }} total</span></p>
            <span class="kpi-link">View all <i class="fas fa-arrow-right"></i></span>
        </a>

    </div>

    {{-- Branch stat row --}}
    <p class="db-sec-title">Quick Stats</p>
    <div class="branch-stat-row">

        <a href="{{ route('admin.user-subcrptions.index') }}" class="stat">
            <div class="stat-icon si-blue"><i class="fas fa-user-plus"></i></div>
            <div>
                <div class="stat-val">{{ number_format($branchTodaySubscriptions) }}</div>
                <div class="stat-label">New Subscriptions Today</div>
            </div>
        </a>

        <a href="{{ route('admin.pause-requests.index') }}" class="stat">
            <div class="stat-icon si-orange"><i class="fas fa-pause-circle"></i></div>
            <div>
                <div class="stat-val">{{ number_format($branchPauseRequests) }}</div>
                <div class="stat-label">Pending Pause Requests</div>
            </div>
        </a>

        <a href="{{ route('admin.meals.index') }}" class="stat">
            <div class="stat-icon" style="background:#fff7ed;color:#ea580c;"><i class="fas fa-utensils"></i></div>
            <div>
                <div class="stat-val">{{ number_format($branchTotalMeals) }}</div>
                <div class="stat-label">Total Meals</div>
            </div>
        </a>

    </div>

    {{-- Branch delivery progress --}}
    <p class="db-sec-title">Delivery Progress</p>
    <div class="db-card" style="margin-bottom:24px;">
        <div class="panel-hdr">
            <div class="panel-hdr-icon" style="background:#f0fdfa;color:#0d9488;"><i class="fas fa-truck"></i></div>
            <h6>Today's Delivery Status</h6>
            <span style="font-size:.75rem;color:#9ca3af;">{{ now()->format('d M Y') }}</span>
        </div>
        <div class="panel-body">
            <div class="del-row">
                <div class="del-box del-total">
                    <div class="del-box-val" style="color:#374151;">{{ $branchDeliveryTotal }}</div>
                    <div class="del-box-label">Scheduled</div>
                </div>
                <div class="del-box del-done">
                    <div class="del-box-val">{{ $branchDelivered }}</div>
                    <div class="del-box-label">Delivered</div>
                </div>
                <div class="del-box del-pend">
                    <div class="del-box-val">{{ $branchPending }}</div>
                    <div class="del-box-label">Pending</div>
                </div>
            </div>
            @if($branchDeliveryTotal > 0)
            <div style="margin-top:16px;">
                <div style="display:flex;justify-content:space-between;font-size:.75rem;color:#9ca3af;margin-bottom:6px;">
                    <span>Completion Rate</span>
                    <span style="font-weight:600;color:#374151;">{{ round($branchDelivered / $branchDeliveryTotal * 100) }}%</span>
                </div>
                <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                    <div style="height:100%;background:linear-gradient(90deg,#22c55e,#16a34a);border-radius:4px;width:{{ round($branchDelivered/$branchDeliveryTotal*100) }}%;transition:width .6s ease;"></div>
                </div>
            </div>
            @endif
            <a href="{{ route('admin.delivery-orders.index') }}" class="del-link">
                <i class="fas fa-external-link-alt"></i> Manage Delivery Orders
            </a>
        </div>
    </div>

    {{-- Branch recent deliveries table --}}
    <p class="db-sec-title">Today's Deliveries</p>
    <div class="db-card">
        <div class="tbl-hdr">
            <div>
                <h6>Today's Delivery Orders</h6>
                <p>All orders scheduled for today in your branch</p>
            </div>
            <a href="{{ route('admin.delivery-orders.index') }}" class="view-all">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        @if($recentDeliveries->count() > 0)
        <div class="table-responsive">
            <table class="db-tbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Delivery Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentDeliveries as $order)
                    <tr>
                        <td style="color:#9ca3af;font-size:.78rem;">#{{ $order->id }}</td>
                        <td style="font-weight:500;">{{ $order->subscription->user->name ?? '—' }}</td>
                        <td style="white-space:nowrap;color:#6b7280;">{{ $order->delivery_date ?? '—' }}</td>
                        <td>
                            @if($order->status === 'delivered')
                                <span class="pill pill-green">Delivered</span>
                            @elseif($order->status === 'pending')
                                <span class="pill" style="background:#fffbeb;color:#d97706;border:1px solid #fde68a;">Pending</span>
                            @else
                                <span class="pill pill-gray">{{ ucfirst($order->status ?? '—') }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.delivery-orders.show', $order->id) }}" class="act-btn" title="View">
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
            <i class="fas fa-truck"></i>
            <p>No deliveries scheduled for today.</p>
        </div>
        @endif
    </div>

    @else
    {{-- ══════════════════════════════════════════
         SUPERADMIN DASHBOARD
    ══════════════════════════════════════════ --}}

    {{-- ── KPI row ── --}}
    <p class="db-sec-title">Key Metrics</p>
    <div class="kpi-row">

        <a href="{{ route('admin.users.index') }}" class="kpi kpi-blue">
            <div class="kpi-top">
                <div class="kpi-icon ki-blue"><i class="fas fa-users"></i></div>
                <span class="kpi-badge">Users</span>
            </div>
            <div class="kpi-value">{{ number_format($totalUsers) }}</div>
            <p class="kpi-label">Total Customers</p>
            <span class="kpi-link">View all <i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="{{ route('admin.user-subcrptions.index') }}" class="kpi kpi-green">
            <div class="kpi-top">
                <div class="kpi-icon ki-green"><i class="fas fa-clipboard-check"></i></div>
                <span class="kpi-badge">Subscriptions</span>
            </div>
            <div class="kpi-value">{{ number_format($activeSubscriptions) }}</div>
            <p class="kpi-label">Active Subscriptions <span style="font-size:.75rem;color:#9ca3af;">/ {{ number_format($totalSubscriptions) }} total</span></p>
            <span class="kpi-link">View all <i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="{{ route('admin.delivery-orders.index') }}" class="kpi kpi-teal">
            <div class="kpi-top">
                <div class="kpi-icon ki-teal"><i class="fas fa-truck"></i></div>
                <span class="kpi-badge">Today</span>
            </div>
            <div class="kpi-value">{{ number_format($todayDeliveryTotal) }}</div>
            <p class="kpi-label">Today's Deliveries <span style="font-size:.75rem;color:#9ca3af;">{{ $todayDelivered }} done</span></p>
            <span class="kpi-link">View orders <i class="fas fa-arrow-right"></i></span>
        </a>

        <div class="kpi kpi-purple" style="cursor:default;">
            <div class="kpi-top">
                <div class="kpi-icon ki-purple"><i class="fas fa-chart-line"></i></div>
                <span class="kpi-badge">This Month</span>
            </div>
            <div class="kpi-value">{{ number_format($monthlySubscriptions) }}</div>
            <p class="kpi-label">New Subscriptions <span style="font-size:.75rem;color:#9ca3af;">{{ $todaySubscriptions }} today</span></p>
        </div>

    </div>

    {{-- ── Stat mini-cards ── --}}
    <p class="db-sec-title">Catalog &amp; Setup</p>
    <div class="stat-row">

        <a href="{{ route('admin.meals.index') }}" class="stat">
            <div class="stat-icon si-orange"><i class="fas fa-utensils"></i></div>
            <div>
                <div class="stat-val">{{ number_format($totalMeals) }}</div>
                <div class="stat-label">Meals</div>
            </div>
        </a>

        <a href="{{ route('admin.categories.index') }}" class="stat">
            <div class="stat-icon si-blue"><i class="fas fa-tags"></i></div>
            <div>
                <div class="stat-val">{{ number_format($totalCategories) }}</div>
                <div class="stat-label">Categories</div>
            </div>
        </a>

        <a href="{{ route('admin.branches.index') }}" class="stat">
            <div class="stat-icon si-indigo"><i class="fas fa-code-branch"></i></div>
            <div>
                <div class="stat-val">{{ number_format($totalBranches) }}</div>
                <div class="stat-label">Branches</div>
            </div>
        </a>

        <a href="{{ route('admin.areas.index') }}" class="stat">
            <div class="stat-icon si-slate"><i class="fas fa-map-marker-alt"></i></div>
            <div>
                <div class="stat-val">{{ number_format($totalAreas) }}</div>
                <div class="stat-label">Areas</div>
            </div>
        </a>

        <a href="{{ route('admin.coupons.index') }}" class="stat">
            <div class="stat-icon si-pink"><i class="fas fa-ticket-alt"></i></div>
            <div>
                <div class="stat-val">{{ number_format($totalCoupons) }}</div>
                <div class="stat-label">Coupons <span style="color:#16a34a;font-weight:600;">{{ $activeCoupons }} active</span></div>
            </div>
        </a>

    </div>

    {{-- ── Middle two-col ── --}}
    <p class="db-sec-title">Today's Snapshot</p>
    <div class="mid-row">

        {{-- Today's activity --}}
        <div class="db-card">
            <div class="panel-hdr">
                <div class="panel-hdr-icon si-blue"><i class="fas fa-bolt"></i></div>
                <h6>Today's Activity</h6>
                <span style="font-size:.75rem;color:#9ca3af;">{{ now()->format('d M Y') }}</span>
            </div>
            <div class="panel-body">
                <div class="activity-item">
                    <div class="act-dot" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-user-plus"></i></div>
                    <div class="act-body">
                        <strong>New Subscriptions</strong>
                        <span>Signed up today</span>
                    </div>
                    <span class="act-count">{{ $todaySubscriptions }}</span>
                </div>
                <div class="activity-item">
                    <div class="act-dot" style="background:#fff7ed;color:#ea580c;"><i class="fas fa-utensils"></i></div>
                    <div class="act-body">
                        <strong>Meals Added</strong>
                        <span>New items in catalog</span>
                    </div>
                    <span class="act-count">{{ $todayMeals }}</span>
                </div>
                <div class="activity-item">
                    <div class="act-dot" style="background:#eff6ff;color:#2563eb;"><i class="fas fa-clipboard-check"></i></div>
                    <div class="act-body">
                        <strong>Active Subscriptions</strong>
                        <span>Currently running</span>
                    </div>
                    <span class="act-count">{{ $activeSubscriptions }}</span>
                </div>
                <div class="activity-item">
                    <div class="act-dot" style="background:#f0fdfa;color:#0d9488;"><i class="fas fa-truck"></i></div>
                    <div class="act-body">
                        <strong>Deliveries Completed</strong>
                        <span>Out of {{ $todayDeliveryTotal }} scheduled</span>
                    </div>
                    <span class="act-count">{{ $todayDelivered }}</span>
                </div>
            </div>
        </div>

        {{-- Delivery status ── --}}
        <div class="db-card">
            <div class="panel-hdr">
                <div class="panel-hdr-icon" style="background:#f0fdfa;color:#0d9488;"><i class="fas fa-truck"></i></div>
                <h6>Today's Delivery Status</h6>
                <span style="font-size:.75rem;color:#9ca3af;">{{ now()->format('d M Y') }}</span>
            </div>
            <div class="panel-body">
                <div class="del-row">
                    <div class="del-box del-total">
                        <div class="del-box-val" style="color:#374151;">{{ $todayDeliveryTotal }}</div>
                        <div class="del-box-label">Scheduled</div>
                    </div>
                    <div class="del-box del-done">
                        <div class="del-box-val">{{ $todayDelivered }}</div>
                        <div class="del-box-label">Delivered</div>
                    </div>
                    <div class="del-box del-pend">
                        <div class="del-box-val">{{ $todayPending }}</div>
                        <div class="del-box-label">Pending</div>
                    </div>
                </div>

                @if($todayDeliveryTotal > 0)
                <div style="margin-top:16px;">
                    <div style="display:flex;justify-content:space-between;font-size:.75rem;color:#9ca3af;margin-bottom:6px;">
                        <span>Completion Rate</span>
                        <span style="font-weight:600;color:#374151;">{{ round($todayDelivered / $todayDeliveryTotal * 100) }}%</span>
                    </div>
                    <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;background:linear-gradient(90deg,#22c55e,#16a34a);border-radius:4px;width:{{ round($todayDelivered/$todayDeliveryTotal*100) }}%;transition:width .6s ease;"></div>
                    </div>
                </div>
                @endif

                <a href="{{ route('admin.delivery-orders.index') }}" class="del-link">
                    <i class="fas fa-external-link-alt"></i> Manage Delivery Orders
                </a>
            </div>
        </div>

    </div>

    {{-- ── Recent subscriptions ── --}}
    <p class="db-sec-title">Recent Activity</p>
    <div class="db-card">
        <div class="tbl-hdr">
            <div>
                <h6>Recent Subscriptions</h6>
                <p>Latest subscription registrations</p>
            </div>
            <a href="{{ route('admin.user-subcrptions.index') }}" class="view-all">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        @if($recentSubscriptions->count() > 0)
        <div class="table-responsive">
            <table class="db-tbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSubscriptions as $sub)
                    <tr>
                        <td style="color:#9ca3af;font-size:.78rem;">#{{ $sub->id }}</td>
                        <td style="font-weight:500;">{{ $sub->user->name ?? '—' }}</td>
                        <td style="color:#6b7280;">{{ $sub->subcrption_plans->title ?? '—' }}</td>
                        <td>
                            @if($sub->status === 'active')
                                <span class="pill pill-green">Active</span>
                            @else
                                <span class="pill pill-gray">{{ ucfirst($sub->status ?? 'Inactive') }}</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;color:#6b7280;">{{ $sub->start_date ?? '—' }}</td>
                        <td style="white-space:nowrap;color:#6b7280;">{{ $sub->end_date ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.user-subcrptions.show', $sub->id) }}" class="act-btn" title="View">
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

    @endif {{-- end $isBranchUser --}}

</div>
@endsection
