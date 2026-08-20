@extends('layouts.admin')
@section('content')

@php
    $isPaused  = $userSubcrption->is_paused;
    $isActive  = $userSubcrption->status === 'active';
    $isPaid    = $userSubcrption->payment === 'paid';

    $pausedAt    = $userSubcrption->paused_at    ? \Carbon\Carbon::parse($userSubcrption->paused_at)    : null;
    $pausedUntil = $userSubcrption->paused_until ? \Carbon\Carbon::parse($userSubcrption->paused_until) : null;
    $isEarlyResume = $pausedUntil && \Carbon\Carbon::now()->lt($pausedUntil);
@endphp

<style>
/* ── Page chrome ── */

/* ── Top header bar ── */
.sv-header {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; flex-wrap: wrap;
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 14px; padding: 20px 24px; margin-bottom: 24px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.sv-header-left { display: flex; align-items: center; gap: 14px; }
.sv-id-badge {
    width: 52px; height: 52px; border-radius: 12px;
    background: linear-gradient(135deg,#3b82f6,#2563eb);
    color: #fff; font-size: 1rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sv-header-title { font-size: 1.2rem; font-weight: 700; color: #111827; line-height: 1.2; }
.sv-header-sub   { font-size: .82rem; color: #6b7280; margin-top: 2px; }
.sv-header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

/* ── Status chips ── */
.sv-chip {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .75rem; font-weight: 600; padding: 4px 10px; border-radius: 20px;
}
.sv-chip-green  { background: #dcfce7; color: #15803d; }
.sv-chip-yellow { background: #fef3c7; color: #b45309; }
.sv-chip-blue   { background: #dbeafe; color: #1d4ed8; }
.sv-chip-gray   { background: #f3f4f6; color: #6b7280; }
.sv-chip-red    { background: #fee2e2; color: #dc2626; }
.sv-chip-orange { background: #ffedd5; color: #c2410c; }

/* ── Section label ── */
.sv-section-title {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: #9ca3af; margin-bottom: 12px;
}

/* ── Info grid ── */
.sv-info-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;
}
@media(max-width:640px){ .sv-info-grid { grid-template-columns: 1fr; } }

.sv-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
    padding: 20px 22px; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.sv-card-title {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: #9ca3af; margin-bottom: 14px;
    display: flex; align-items: center; gap: 7px;
}
.sv-card-title i { font-size: .8rem; }

.sv-kv { display: flex; flex-direction: column; gap: 10px; }
.sv-kv-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
.sv-kv-key { font-size: .8rem; color: #6b7280; white-space: nowrap; flex-shrink: 0; }
.sv-kv-val { font-size: .85rem; color: #111827; font-weight: 500; text-align: right; }

.sv-price { font-size: 1.1rem; font-weight: 700; color: #111827; }

/* ── Pause banner ── */
.sv-pause-banner {
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
    padding: 14px 18px; margin-bottom: 24px;
    display: flex; align-items: flex-start; gap: 12px;
}
.sv-pause-banner i { color: #d97706; font-size: 1rem; margin-top: 2px; flex-shrink: 0; }
.sv-pause-banner-body { font-size: .83rem; color: #92400e; }
.sv-pause-banner-body strong { display: block; margin-bottom: 4px; }

/* ── Action buttons ── */
.sv-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: .82rem; font-weight: 600; padding: 8px 16px;
    border-radius: 8px; border: none; cursor: pointer; text-decoration: none;
    transition: opacity .15s, transform .12s;
}
.sv-btn:hover { opacity: .87; transform: translateY(-1px); text-decoration: none; }
.sv-btn-back    { background: #f3f4f6; color: #374151; }
.sv-btn-edit    { background: #dbeafe; color: #1d4ed8; }
.sv-btn-pause   { background: #fef3c7; color: #b45309; }
.sv-btn-resume  { background: #dcfce7; color: #15803d; }
.sv-btn-logs    { background: #ede9fe; color: #5b21b6; }
.sv-btn-danger  { background: #fee2e2; color: #dc2626; }

/* ── Days grid ── */
.sv-days-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.sv-day-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.sv-day-header {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e5e7eb;
}
.sv-day-dot {
    width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; flex-shrink: 0;
}
.sv-day-name { font-size: .88rem; font-weight: 700; color: #1e293b; text-transform: capitalize; }
.sv-day-count { margin-left: auto; font-size: .72rem; color: #6b7280; }

.sv-day-body { padding: 12px 16px; display: flex; flex-direction: column; gap: 10px; }

/* ── Meal row ── */
.sv-meal-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px; background: #f9fafb;
    border: 1px solid #f3f4f6; border-radius: 10px;
    transition: border-color .15s, box-shadow .15s;
}
.sv-meal-row:hover { border-color: #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,.06); }

.sv-meal-thumb {
    width: 44px; height: 44px; border-radius: 8px; object-fit: cover; flex-shrink: 0;
    background: #e5e7eb;
}
.sv-meal-thumb-placeholder {
    width: 44px; height: 44px; border-radius: 8px; flex-shrink: 0;
    background: linear-gradient(135deg,#e0e7ff,#c7d2fe);
    display: flex; align-items: center; justify-content: center;
    color: #6366f1; font-size: .9rem;
}
.sv-meal-info { flex: 1; min-width: 0; }
.sv-meal-title { font-size: .84rem; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sv-meal-macros { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
.sv-macro {
    font-size: .68rem; color: #6b7280; background: #fff; border: 1px solid #e5e7eb;
    padding: 2px 7px; border-radius: 20px; display: inline-flex; align-items: center; gap: 3px;
}
.sv-macro i { font-size: .62rem; }
.sv-meal-extras { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px; }
.sv-extra-chip { font-size: .68rem; color: #0f766e; background: #f0fdfa; border: 1px solid #99f6e4;
                 padding: 2px 8px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px; }
.sv-extra-chip i { font-size: .6rem; }
.sv-extra-chip strong { font-weight: 700; }
.sv-meal-type-chip {
    font-size: .68rem; font-weight: 700; padding: 3px 9px; border-radius: 20px; flex-shrink: 0;
}
.sv-type-meal  { background: #dbeafe; color: #1d4ed8; }
.sv-type-snack { background: #fef3c7; color: #b45309; }

/* ── Remove meal button ── */
.rm-meal-btn {
    width: 26px; height: 26px; border-radius: 6px; border: none; cursor: pointer;
    background: #fee2e2; color: #dc2626; font-size: .7rem;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s, transform .1s; flex-shrink: 0;
}
.rm-meal-btn:hover { background: #fecaca; transform: scale(1.1); }

/* ── Empty states ── */
.sv-empty {
    text-align: center; padding: 28px 16px; color: #9ca3af; font-size: .83rem;
}
.sv-empty i { font-size: 1.4rem; display: block; margin-bottom: 8px; }

/* ── Alert ── */
.sv-alert {
    border-radius: 10px; padding: 12px 16px; font-size: .83rem; margin-bottom: 16px;
    display: flex; align-items: flex-start; gap: 10px;
}
.sv-alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.sv-alert-error   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

/* ── Add Meal Modal ── */
.am-modal-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 1060;
    align-items: center; justify-content: center;
}
.am-modal-backdrop.open { display: flex; }
.am-modal {
    background: #fff; border-radius: 16px; width: 100%; max-width: 460px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2); overflow: hidden;
    animation: amSlideIn .2s ease;
}
@keyframes amSlideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to   { transform: translateY(0);     opacity: 1; }
}
.am-modal-header {
    background: linear-gradient(135deg,#16a34a,#15803d);
    padding: 18px 22px; display: flex; align-items: center; gap: 12px;
}
.am-modal-header-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(255,255,255,.2); display: flex; align-items: center;
    justify-content: center; font-size: 1rem; color: #fff; flex-shrink: 0;
}
.am-modal-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
.am-modal-sub   { font-size: .78rem; color: rgba(255,255,255,.8); margin-top: 2px; }
.am-modal-body  { padding: 22px; display: flex; flex-direction: column; gap: 16px; }
.am-group label { font-size: .78rem; font-weight: 600; color: #374151; display: block; margin-bottom: 5px; }
.am-group label .req { color: #ef4444; }
.am-plan-note {
    padding: 10px 14px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px;
    font-size: .82rem; color: #075985; display: flex; align-items: center; gap: 8px;
}
.am-day-block {
    border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 14px;
    display: flex; flex-direction: column; gap: 10px; background: #fafafa;
}
.am-day-title {
    font-size: .9rem; font-weight: 800; color: #1e293b; text-transform: capitalize;
    display: flex; align-items: center; gap: 8px;
}
.am-day-dot { width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; flex-shrink: 0; }
.am-slot-heading {
    font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em;
    color: #1d4ed8; display: flex; align-items: center; gap: 6px; margin: 0;
}
.am-select {
    width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 9px;
    font-size: .87rem; color: #111827; background: #fff;
    transition: border-color .15s, box-shadow .15s; outline: none;
}
.am-select:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
/* Select2 (meal search) theming */
.select2-container--default .select2-selection--single {
    height: auto; border: 1px solid #d1d5db; border-radius: 9px;
    padding: 9px 12px; transition: border-color .15s, box-shadow .15s;
}
.select2-container--default.select2-container--open .select2-selection--single,
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12);
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 1.3; font-size: .87rem; color: #111827; padding: 0 24px 0 2px;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9ca3af; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 100%; top: 0; right: 8px; }
.select2-dropdown {
    border: 1px solid #d1d5db; border-radius: 9px; overflow: hidden;
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
}
.select2-container--default .select2-search--dropdown {
    padding: 10px; border-bottom: 1px solid #f3f4f6;
}
.select2-container--default .select2-search--dropdown .select2-search__field {
    padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px;
    font-size: .85rem; outline: none;
}
.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12);
}
.select2-container--default .select2-results__option {
    padding: 9px 16px; font-size: .85rem; color: #374151;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: #16a34a; color: #fff;
}
.select2-container--default .select2-results__option[aria-selected=true] {
    background: #f0fdf4; color: #15803d;
}
.select2-results__group { padding: 8px 16px; }

.am-type-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.am-type-opt {
    display: flex; align-items: center; gap: 8px; padding: 10px 14px;
    border: 1px solid #e5e7eb; border-radius: 9px; cursor: pointer;
    font-size: .84rem; font-weight: 600; color: #374151;
    transition: border-color .15s, background .15s;
}
.am-type-opt:has(input:checked) { border-color: #16a34a; background: #f0fdf4; color: #15803d; }
.am-type-opt input { accent-color: #16a34a; }
.am-modal-footer {
    padding: 16px 22px; border-top: 1px solid #f3f4f6;
    display: flex; gap: 10px; justify-content: flex-end;
}
.am-btn-submit {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 20px; border-radius: 9px; border: none; cursor: pointer;
    font-size: .86rem; font-weight: 600; color: #fff;
    background: linear-gradient(135deg,#16a34a,#15803d); transition: opacity .15s;
}
.am-btn-submit:hover { opacity: .88; }
.am-btn-cancel {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 16px; border-radius: 9px; border: 1px solid #e5e7eb; cursor: pointer;
    font-size: .86rem; font-weight: 600; color: #6b7280; background: #fff;
    transition: background .15s;
}
.am-btn-cancel:hover { background: #f9fafb; }
</style>

<div class="sv-page">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="sv-alert sv-alert-success mb-4">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="sv-alert sv-alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Header ── --}}
    <div class="sv-header">
        <div class="sv-header-left">
            <div class="sv-id-badge">#{{ $userSubcrption->id }}</div>
            <div>
                <div class="sv-header-title">{{ $userSubcrption->user->name ?? 'Unknown User' }}</div>
                <div class="sv-header-sub">{{ $userSubcrption->subcrption_plans->title ?? 'No Plan' }}</div>
            </div>
            <span class="sv-chip {{ $isActive && !$isPaused ? 'sv-chip-green' : ($isPaused ? 'sv-chip-yellow' : 'sv-chip-gray') }}">
                <i class="fas fa-circle" style="font-size:.45rem;"></i>
                {{ $isPaused ? 'Paused' : ucfirst($userSubcrption->status ?? 'inactive') }}
            </span>
            <span class="sv-chip {{ $isPaid ? 'sv-chip-blue' : 'sv-chip-orange' }}">
                <i class="fas {{ $isPaid ? 'fa-check' : 'fa-clock' }}"></i>
                {{ ucfirst($userSubcrption->payment ?? 'pending') }}
            </span>
        </div>
        <div class="sv-header-actions">
            @if($isPaused)
                <button type="button" class="sv-btn sv-btn-resume" data-toggle="modal" data-target="#resumeModal">
                    <i class="fas fa-play"></i> Resume
                </button>
            @endif
            @if($userSubcrption->pause_logs()->count() > 0)
                <a href="{{ route('admin.user-subcrptions.pause-logs', $userSubcrption->id) }}" class="sv-btn sv-btn-logs">
                    <i class="fas fa-history"></i> Pause Logs
                </a>
            @endif
            @can('user_subcrption_edit')
                <button type="button" class="sv-btn" style="background:#dcfce7;color:#15803d;" id="addMealBtn">
                    <i class="fas fa-utensils"></i> Manage Meals
                </button>
            @endcan
            <a href="{{ route('admin.user-subcrptions.index') }}" class="sv-btn sv-btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- ── Pause banner ── --}}
    @if($isPaused)
    <div class="sv-pause-banner">
        <i class="fas fa-pause-circle"></i>
        <div class="sv-pause-banner-body">
            <strong>Subscription is currently paused</strong>
            Paused on {{ $pausedAt ? $pausedAt->format('d M Y') : 'N/A' }}
            @if($pausedUntil) · Scheduled to resume {{ $pausedUntil->format('d M Y') }} @endif
            · {{ $userSubcrption->total_paused_days ?? 0 }} total paused days
        </div>
    </div>
    @endif

    {{-- ── Info cards ── --}}
    <div class="sv-info-grid">

        {{-- Subscription Info --}}
        <div class="sv-card">
            <div class="sv-card-title"><i class="fas fa-id-card"></i> Subscription Info</div>
            <div class="sv-kv">
                <div class="sv-kv-row">
                    <span class="sv-kv-key">Plan</span>
                    <span class="sv-kv-val">{{ $userSubcrption->subcrption_plans->title ?? '—' }}</span>
                </div>

                <div class="sv-kv-row">
                    <span class="sv-kv-key">Start Date</span>
                    <span class="sv-kv-val">{{ $userSubcrption->start_date ?? '—' }}</span>
                </div>
                <div class="sv-kv-row">
                    <span class="sv-kv-key">End Date</span>
                    <span class="sv-kv-val">{{ $userSubcrption->end_date ?? '—' }}</span>
                </div>
                @if($userSubcrption->original_end_date)
                <div class="sv-kv-row">
                    <span class="sv-kv-key">Original End</span>
                    <span class="sv-kv-val">{{ \Carbon\Carbon::parse($userSubcrption->original_end_date)->format('Y-m-d') }}</span>
                </div>
                @endif
                <div class="sv-kv-row">
                    <span class="sv-kv-key">Price</span>
                    <span class="sv-kv-val sv-price">{{ number_format($userSubcrption->price ?? 0, 3) }} KWD</span>
                </div>
            </div>
        </div>

        {{-- Customer & Extra Info --}}
        <div class="sv-card">
            <div class="sv-card-title"><i class="fas fa-user"></i> Customer & Settings</div>
            <div class="sv-kv">
                <div class="sv-kv-row">
                    <span class="sv-kv-key">Customer</span>
                    <span class="sv-kv-val">{{ $userSubcrption->user->name ?? '—' }}</span>
                </div>
                <div class="sv-kv-row">
                    <span class="sv-kv-key">Personalized</span>
                    <span class="sv-kv-val">
                        @if($userSubcrption->is_personalized)
                            <span class="sv-chip sv-chip-blue">Yes</span>
                        @else
                            <span class="sv-chip sv-chip-gray">No</span>
                        @endif
                    </span>
                </div>
                @if($userSubcrption->is_personalized)
                <div class="sv-kv-row">
                    <span class="sv-kv-key">Protein Target</span>
                    <span class="sv-kv-val">{{ $userSubcrption->protein ?? '—' }}</span>
                </div>
                <div class="sv-kv-row">
                    <span class="sv-kv-key">Carbs Target</span>
                    <span class="sv-kv-val">{{ $userSubcrption->carbs ?? '—' }}</span>
                </div>
                @endif
                <div class="sv-kv-row">
                    <span class="sv-kv-key">Selected Days</span>
                    <span class="sv-kv-val">
                        @if($userSubcrption->selected_days)
                            @php
                                $days = is_array($userSubcrption->selected_days)
                                    ? $userSubcrption->selected_days
                                    : explode(',', $userSubcrption->selected_days);
                            @endphp
                            {{ implode(', ', array_map(fn($d) => ucfirst(trim($d)), $days)) }}
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="sv-kv-row">
                    <span class="sv-kv-key">Total Meals Days</span>
                    <span class="sv-kv-val">{{ $subscriptionDays->count() }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Subscription Days & Meals ── --}}
    <div class="sv-section-title"><i class="fas fa-calendar-alt mr-1"></i> Subscription Days &amp; Meals</div>

    @if($subscriptionDays->count() > 0)
        <div class="sv-days-grid">
            @foreach($subscriptionDays as $day)
                @php
                    $dayMeals  = $day->subscription_meals->where('type', 'is meal');
                    $daySnacks = $day->subscription_meals->where('type', 'is snack');
                    $total     = $day->subscription_meals->count();
                @endphp
                <div class="sv-day-card">
                    <div class="sv-day-header">
                        <div class="sv-day-dot"></div>
                        <span class="sv-day-name">{{ ucfirst($day->day) }}</span>
                        <span class="sv-day-count">
                            @if($mealLimit || $snackLimit)
                                {{ $dayMeals->count() }}/{{ $mealLimit ?? '∞' }} meals
                                &nbsp;·&nbsp;
                                {{ $daySnacks->count() }}/{{ $snackLimit ?? '∞' }} snacks
                            @else
                                {{ $total }} item{{ $total != 1 ? 's' : '' }}
                            @endif
                        </span>
                    </div>
                    <div class="sv-day-body">
                        @forelse($day->subscription_meals as $sm)
                            <div class="sv-meal-row">
                                @if($sm->meal && $sm->meal->getFirstMedia('image'))
                                    <img src="{{ $sm->meal->getFirstMediaUrl('image', 'thumb') }}" alt="" class="sv-meal-thumb">
                                @else
                                    <div class="sv-meal-thumb-placeholder"><i class="fas fa-utensils"></i></div>
                                @endif
                                <div class="sv-meal-info">
                                    <div class="sv-meal-title">{{ $sm->meal->title ?? 'Unknown Meal' }}</div>
                                    <div class="sv-meal-macros">
                                        @if($sm->meal?->calories)
                                            <span class="sv-macro"><i class="fas fa-fire"></i> {{ $sm->meal->calories }} cal</span>
                                        @endif
                                        @if($sm->meal?->protein_g)
                                            <span class="sv-macro"><i class="fas fa-dumbbell"></i> {{ $sm->meal->protein_g }}g</span>
                                        @endif
                                        @if($sm->meal?->carbs_g)
                                            <span class="sv-macro"><i class="fas fa-bread-slice"></i> {{ $sm->meal->carbs_g }}g</span>
                                        @endif
                                        @if($sm->meal?->fat_g)
                                            <span class="sv-macro"><i class="fas fa-tint"></i> {{ $sm->meal->fat_g }}g</span>
                                        @endif
                                    </div>
                                    @if($sm->selectedIngredients->isNotEmpty())
                                        <div class="sv-meal-extras">
                                            @foreach($sm->selectedIngredients->groupBy('meal_extra_id') as $extraIngredients)
                                                <span class="sv-extra-chip">
                                                    <i class="fas fa-plus-circle"></i>
                                                    <strong>{{ $extraIngredients->first()->mealExtra?->name ?? 'Extra' }}:</strong>
                                                    {{ $extraIngredients->pluck('name')->implode(', ') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <span class="sv-meal-type-chip {{ $sm->type === 'is meal' ? 'sv-type-meal' : 'sv-type-snack' }}">
                                    {{ $sm->type === 'is meal' ? 'Meal' : 'Snack' }}
                                </span>
                                @can('user_subcrption_edit')
                                <form action="{{ route('admin.user-subcrptions.remove-meal', [$userSubcrption->id, $sm->id]) }}"
                                      method="POST" class="rm-meal-form" style="flex-shrink:0;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="rm-meal-btn swal-rm-meal"
                                        title="Remove meal"
                                        data-name="{{ $sm->meal->title ?? 'this meal' }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        @empty
                            <div class="sv-empty">
                                <i class="fas fa-utensils"></i>
                                No meals assigned
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="sv-card">
            <div class="sv-empty" style="padding:40px;">
                <i class="fas fa-calendar-times" style="font-size:2rem;color:#d1d5db;display:block;margin-bottom:12px;"></i>
                No subscription days found for this subscription.
            </div>
        </div>
    @endif

</div>

{{-- ── Resume Modal ── --}}
@if($isPaused)
<div class="modal fade" id="resumeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:14px; border:none; box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6; padding:18px 22px;">
                <h5 class="modal-title" style="font-weight:700; color:#111827;">
                    <i class="fas fa-play-circle text-success mr-2"></i> Resume Subscription
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('admin.user-subcrptions.resume', $userSubcrption->id) }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px 22px;">
                    <div class="sv-pause-banner" style="margin-bottom:14px;">
                        <i class="fas fa-info-circle"></i>
                        <div class="sv-pause-banner-body">
                            <strong>Current pause details</strong>
                            Paused: {{ $pausedAt ? $pausedAt->format('d M Y') : 'N/A' }} ·
                            Until: {{ $pausedUntil ? $pausedUntil->format('d M Y') : 'N/A' }} ·
                            {{ $userSubcrption->total_paused_days ?? 0 }} days total
                        </div>
                    </div>
                    @if($isEarlyResume)
                        <div class="sv-alert" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;border-radius:10px;padding:10px 14px;margin-bottom:12px;font-size:.82rem;">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Early resume:</strong> End date will be adjusted for the actual days paused.
                        </div>
                    @endif
                    <div class="form-group mb-0">
                        <label style="font-size:.82rem; font-weight:600; color:#374151;">Notes (optional)</label>
                        <textarea class="form-control" name="notes" rows="2" maxlength="1000" placeholder="Reason for resuming early, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6; padding:14px 22px;">
                    <button type="button" class="sv-btn sv-btn-back" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="sv-btn sv-btn-resume"><i class="fas fa-play"></i> Resume Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ── Add Meal Modal ── --}}
<div class="am-modal-backdrop" id="addMealBackdrop">
    <div class="am-modal" style="max-width:560px;">
        <div class="am-modal-header">
            <div class="am-modal-header-icon"><i class="fas fa-utensils"></i></div>
            <div>
                <div class="am-modal-title">Manage Meals, All Days</div>
                <div class="am-modal-sub">{{ $userSubcrption->user->name ?? 'Customer' }}</div>
            </div>
        </div>
        @php
            // Slot count per day comes from the plan; fall back to one each when unset.
            $mealSlots  = $mealLimit  ?: 1;
            $snackSlots = $snackLimit ?: 1;

            // Plain-language summary of what the plan includes.
            $incParts = [];
            if ($mealLimit)  { $incParts[] = $mealLimit  . ' meal'  . ($mealLimit  > 1 ? 's' : ''); }
            if ($snackLimit) { $incParts[] = $snackLimit . ' snack' . ($snackLimit > 1 ? 's' : ''); }
            $includesText = count($incParts) ? implode(' and ', $incParts) . ' per day' : 'meals and snacks';
        @endphp
        <form method="POST" action="{{ route('admin.user-subcrptions.add-meals', $userSubcrption->id) }}">
            @csrf
            <div class="am-modal-body" style="max-height:72vh;overflow-y:auto;">
                {{-- Plan summary (plain text, no pill buttons) --}}
                <div class="am-plan-note">
                    <i class="fas fa-info-circle"></i>
                    <span>This subscription includes only <strong>{{ $includesText }}</strong>.</span>
                </div>

                {{-- One block per day, pre-filled with what's already assigned --}}
                @foreach($formDays as $day)
                    @php
                        $dayMealIds  = $existingByDay[$day]['is meal']  ?? [];
                        $daySnackIds = $existingByDay[$day]['is snack'] ?? [];
                        $dMealSlots  = max($mealSlots,  count($dayMealIds));
                        $dSnackSlots = max($snackSlots, count($daySnackIds));
                    @endphp
                    <div class="am-day-block">
                        <div class="am-day-title"><span class="am-day-dot"></span>{{ ucfirst($day) }}</div>
                        <input type="hidden" name="form_days[]" value="{{ $day }}">

                        <div class="am-slot-heading"><i class="fas fa-utensils"></i> Main Meals</div>
                        @for($i = 0; $i < $dMealSlots; $i++)
                        <div class="am-group">
                            <select name="meals[{{ $day }}][]" class="am-select select2-meal">
                                <option value="">— Meal {{ $i + 1 }} (optional) —</option>
                                @foreach($mainMeals as $id => $title)
                                    <option value="{{ $id }}" {{ (isset($dayMealIds[$i]) && $dayMealIds[$i] == $id) ? 'selected' : '' }}>{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endfor

                        <div class="am-slot-heading" style="color:#b45309;margin-top:4px;"><i class="fas fa-apple-alt"></i> Snacks</div>
                        @for($i = 0; $i < $dSnackSlots; $i++)
                        <div class="am-group">
                            <select name="snacks[{{ $day }}][]" class="am-select select2-meal">
                                <option value="">— Snack {{ $i + 1 }} (optional) —</option>
                                @foreach($snackMeals as $id => $title)
                                    <option value="{{ $id }}" {{ (isset($daySnackIds[$i]) && $daySnackIds[$i] == $id) ? 'selected' : '' }}>{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endfor
                    </div>
                @endforeach

                <p style="font-size:.75rem;color:#9ca3af;margin:0;">
                    <i class="fas fa-info-circle"></i> Slots already assigned are pre-selected. Saving replaces each day with exactly what's shown here, clear a slot to remove that meal.
                </p>
            </div>
            <div class="am-modal-footer">
                <button type="button" class="am-btn-cancel" id="amCancelBtn"><i class="fas fa-times"></i> Cancel</button>
                <button type="submit" class="am-btn-submit"><i class="fas fa-save"></i> Save Meals</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
// Remove meal confirmation
document.querySelectorAll('.swal-rm-meal').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var name = this.dataset.name || 'this meal';
        var form = this.closest('.rm-meal-form');
        Swal.fire({
            title: 'Remove meal?',
            html: 'Remove <strong>' + name + '</strong> from this day?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        }).then(function (result) {
            if (result.isConfirmed) form.submit();
        });
    });
});

var backdrop = document.getElementById('addMealBackdrop');

document.getElementById('addMealBtn').addEventListener('click', function () {
    backdrop.classList.add('open');
});

document.getElementById('amCancelBtn').addEventListener('click', function () {
    backdrop.classList.remove('open');
});

backdrop.addEventListener('click', function (e) {
    if (e.target === backdrop) backdrop.classList.remove('open');
});

$(document).ready(function () {
    $('.select2-meal').select2({
        dropdownParent: $('#addMealBackdrop'),
        placeholder: 'Search meal...',
        allowClear: true,
        width: '100%',
    });
});
</script>
@endsection

@endsection
