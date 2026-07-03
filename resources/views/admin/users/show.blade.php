@extends('layouts.admin')
@section('content')

<style>
 .content-wrapper { background: #f9fafb !important; }

.sv-header {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; flex-wrap: wrap;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 16px; padding: 24px 28px; margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(37,99,235,.18);
}
.sv-header-left { display: flex; align-items: center; gap: 16px; }
.sv-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    color: #fff; font-size: 1.3rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sv-header-title { font-size: 1.15rem; font-weight: 700; color: #fff; line-height: 1.2; }
.sv-header-sub   { font-size: .82rem; color: rgba(255,255,255,.8); margin-top: 2px; }
.sv-header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.sv-chip {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .75rem; font-weight: 600; padding: 4px 10px; border-radius: 20px;
}
.sv-chip-green  { background: #dcfce7; color: #15803d; }
.sv-chip-red    { background: #fee2e2; color: #dc2626; }
.sv-chip-blue   { background: #dbeafe; color: #1d4ed8; }
.sv-chip-gray   { background: #f3f4f6; color: #6b7280; }
.sv-chip-orange { background: #ffedd5; color: #c2410c; }
.sv-chip-violet { background: #ede9fe; color: #5b21b6; }
.sv-chip-teal   { background: #ccfbf1; color: #0d9488; }

.sv-info-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;
}
@media(max-width:640px){ .sv-info-grid { grid-template-columns: 1fr; } }

.sv-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
    padding: 20px 22px; box-shadow: 0 1px 6px rgba(0,0,0,.05);
}
.sv-card-full {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
    padding: 20px 22px; box-shadow: 0 1px 6px rgba(0,0,0,.05);
    margin-bottom: 24px;
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

.sv-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: .82rem; font-weight: 600; padding: 8px 16px;
    border-radius: 8px; border: none; cursor: pointer; text-decoration: none;
    transition: opacity .15s, transform .12s;
}
.sv-btn:hover { opacity: .87; transform: translateY(-1px); text-decoration: none; }
.sv-btn-back  { background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.3); }
.sv-btn-back:hover { background: rgba(255,255,255,.25); color: #fff; }
.sv-btn-edit  { background: #fff; color: #2563eb; }

.sv-section-title {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: #9ca3af; margin-bottom: 12px;
    display: flex; align-items: center; gap: 6px;
}

.sv-table { width: 100%; border-collapse: collapse; }
.sv-table th {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; color: #6b7280; background: #f9fafb;
    border-bottom: 1px solid #e5e7eb; padding: 10px 14px; white-space: nowrap;
}
.sv-table td {
    font-size: .83rem; color: #374151; vertical-align: middle;
    padding: 10px 14px; border-bottom: 1px solid #f3f4f6;
}
.sv-table tbody tr:hover { background: #fafafa; }
.sv-table tbody tr:last-child td { border-bottom: none; }

.sv-tag {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .72rem; font-weight: 600; padding: 3px 9px;
    border-radius: 20px; margin: 2px;
}
.sv-tag-red    { background: #fee2e2; color: #dc2626; }
.sv-tag-orange { background: #ffedd5; color: #c2410c; }

.sv-empty-row td {
    text-align: center; padding: 28px; color: #9ca3af; font-size: .83rem;
}
</style>

@php
    $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
    $isCustomer = $user->roles->isEmpty();
@endphp

{{-- ── Header ── --}}
<div class="sv-header">
    <div class="sv-header-left">
        <div class="sv-avatar">{{ $initial }}</div>
        <div>
            <div class="sv-header-title">{{ $user->name ?? 'Unknown User' }}</div>
            <div class="sv-header-sub">
                {{ $user->country_code ? '+' . $user->country_code . ' ' : '' }}{{ $user->mobile ?? $user->email ?? '—' }}
            </div>
        </div>
        @if($isCustomer)
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:20px;background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.35);">
                <i class="fas fa-circle" style="font-size:.4rem;"></i> Customer
            </span>
        @else
            @foreach($user->roles as $role)
                <span style="display:inline-flex;align-items:center;gap:5px;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:20px;background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.35);">
                    {{ $role->title }}
                </span>
            @endforeach
        @endif
    </div>
    <div class="sv-header-actions">
        @can('user_edit')
        <a href="{{ route('admin.users.edit', $user->id) }}" class="sv-btn sv-btn-edit">
            <i class="fas fa-pen"></i> Edit
        </a>
        @endcan
        <a href="{{ route('admin.users.index') }}" class="sv-btn sv-btn-back">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- ── Info cards ── --}}
<div class="sv-info-grid">

    {{-- Personal Info --}}
    <div class="sv-card">
        <div class="sv-card-title"><i class="fas fa-user"></i> Personal Info</div>
        <div class="sv-kv">
            <div class="sv-kv-row">
                <span class="sv-kv-key">ID</span>
                <span class="sv-kv-val">#{{ $user->id }}</span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Name</span>
                <span class="sv-kv-val">{{ $user->name ?? '—' }}</span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Email</span>
                <span class="sv-kv-val" style="word-break:break-all;">{{ $user->email ?? '—' }}</span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Mobile</span>
                <span class="sv-kv-val">{{ $user->country_code ? '+' . $user->country_code . ' ' : '' }}{{ $user->mobile ?? '—' }}</span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Gender</span>
                <span class="sv-kv-val">
                    @if($user->gender)
                        <span class="sv-chip {{ $user->gender === 'male' ? 'sv-chip-blue' : 'sv-chip-green' }}">{{ ucfirst($user->gender) }}</span>
                    @else
                        <span style="color:#9ca3af;">—</span>
                    @endif
                </span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Date of Birth</span>
                <span class="sv-kv-val">{{ $user->dob ?? '—' }}</span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Joined</span>
                <span class="sv-kv-val">{{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Health & Goals --}}
    <div class="sv-card">
        <div class="sv-card-title"><i class="fas fa-heartbeat"></i> Health &amp; Goals</div>
        <div class="sv-kv">
            <div class="sv-kv-row">
                <span class="sv-kv-key">Height</span>
                <span class="sv-kv-val">{{ $user->height ? $user->height . ' cm' : '—' }}</span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Weight</span>
                <span class="sv-kv-val">{{ $user->weight ? $user->weight . ' kg' : '—' }}</span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Activity Level</span>
                <span class="sv-kv-val">{{ $user->activity_level ? ucwords(str_replace('_', ' ', $user->activity_level)) : '—' }}</span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Goal</span>
                <span class="sv-kv-val">{{ $user->goal ? ucwords(str_replace('_', ' ', $user->goal)) : '—' }}</span>
            </div>
            <div class="sv-kv-row">
                <span class="sv-kv-key">Affiliated Code</span>
                <span class="sv-kv-val">
                    @if($user->affiliatedCode)
                        <span class="sv-chip sv-chip-teal">{{ $user->affiliatedCode->code }}</span>
                    @else
                        <span style="color:#9ca3af;">None</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

</div>

{{-- ── Dietary Preferences ── --}}
<div class="sv-info-grid">

    {{-- Allergies --}}
    <div class="sv-card">
        <div class="sv-card-title"><i class="fas fa-exclamation-triangle"></i> Allergies</div>
        <div class="sv-kv">
            <div class="sv-kv-row">
                <span class="sv-kv-key">Has Food Allergies</span>
                <span class="sv-kv-val">
                    @if($user->has_food_allergies)
                        <span class="sv-chip sv-chip-red"><i class="fas fa-check"></i> Yes</span>
                    @else
                        <span class="sv-chip sv-chip-gray">No</span>
                    @endif
                </span>
            </div>
        </div>
        @if($user->has_food_allergies && !empty($user->allergies))
        <div style="margin-top:14px;">
            @foreach($user->allergies as $allergy)
                <span class="sv-tag sv-tag-red"><i class="fas fa-exclamation-circle" style="font-size:.6rem;"></i> {{ $allergy }}</span>
            @endforeach
        </div>
        @elseif(!$user->has_food_allergies)
        <p style="margin-top:12px;font-size:.82rem;color:#9ca3af;">No food allergies reported.</p>
        @endif
    </div>

    {{-- Dislikes --}}
    <div class="sv-card">
        <div class="sv-card-title"><i class="fas fa-thumbs-down"></i> Dislikes</div>
        @if(!empty($user->dislikes))
            @foreach($user->dislikes as $dislike)
                <span class="sv-tag sv-tag-orange"><i class="fas fa-times-circle" style="font-size:.6rem;"></i> {{ $dislike }}</span>
            @endforeach
        @else
            <p style="font-size:.82rem;color:#9ca3af;margin:0;">No food dislikes set.</p>
        @endif
    </div>

</div>

{{-- ── Addresses ── --}}
<div class="sv-card-full">
    <div class="sv-section-title"><i class="fas fa-map-marker-alt"></i> Addresses</div>
    <div class="table-responsive">
        <table class="sv-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Area</th>
                    <th>Street</th>
                    <th>Building</th>
                    <th>Floor / Apt</th>
                    <th>Delivery Slot</th>
                    <th>Primary</th>
                </tr>
            </thead>
            <tbody>
                @forelse($user->addresses as $address)
                <tr>
                    <td>
                        <span class="sv-chip sv-chip-gray">{{ $address->category ?? '—' }}</span>
                    </td>
                    <td>{{ $address->phone_number ?? '—' }}</td>
                    <td>{{ $address->area ?? '—' }}</td>
                    <td>{{ $address->street ?? '—' }}</td>
                    <td>{{ $address->house_building ?? '—' }}</td>
                    <td>{{ $address->floor_apartment ?? '—' }}</td>
                    <td>{{ $address->preferred_delivery_slot ?? '—' }}</td>
                    <td>
                        @if($address->is_primary)
                            <span class="sv-chip sv-chip-green"><i class="fas fa-check"></i> Yes</span>
                        @else
                            <span style="color:#9ca3af;">No</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr class="sv-empty-row">
                    <td colspan="8">
                        <i class="fas fa-map-marker-alt" style="display:block;font-size:1.5rem;color:#d1d5db;margin-bottom:8px;"></i>
                        No addresses on file.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Payment Cards ── --}}
<div class="sv-card-full">
    <div class="sv-section-title"><i class="fas fa-credit-card"></i> Payment Cards</div>
    <div class="table-responsive">
        <table class="sv-table">
            <thead>
                <tr>
                    <th>Card Holder</th>
                    <th>Brand</th>
                    <th>Last 4</th>
                    <th>Expiry</th>
                </tr>
            </thead>
            <tbody>
                @forelse($user->paymentMethods as $card)
                <tr>
                    <td style="font-weight:600;">{{ $card->card_holder_name ?? '—' }}</td>
                    <td>
                        <span class="sv-chip sv-chip-gray">{{ strtoupper($card->card_brand ?? '—') }}</span>
                    </td>
                    <td style="font-family:monospace;letter-spacing:.1em;">•••• {{ $card->card_last_four ?? '——' }}</td>
                    <td>{{ $card->card_expiry_month ?? '—' }}/{{ $card->card_expiry_year ?? '—' }}</td>
                </tr>
                @empty
                <tr class="sv-empty-row">
                    <td colspan="4">
                        <i class="fas fa-credit-card" style="display:block;font-size:1.5rem;color:#d1d5db;margin-bottom:8px;"></i>
                        No payment cards on file.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
