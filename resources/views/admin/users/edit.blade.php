@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
@php
    $isCustomer = $user->roles->isEmpty();
@endphp
<style>
    .content-wrapper { background: #f9fafb !important; }
    .cf-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
        box-shadow: 0 1px 6px rgba(0,0,0,.05); overflow: hidden; max-width: 820px; margin: 0 auto;
    }
    .cf-header {
        background: linear-gradient(135deg, #7c3aed, #5b21b6);
        padding: 24px 28px; display: flex; align-items: center; gap: 16px;
    }
    .cf-header-icon {
        width: 48px; height: 48px; border-radius: 12px;
        background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; color: #fff; flex-shrink: 0;
    }
    .cf-header-title { font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0; }
    .cf-header-sub   { font-size: .82rem; color: rgba(255,255,255,.8); margin-top: 2px; }

    .cf-body  { padding: 28px; }
    .cf-section-label {
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: #9ca3af; margin: 24px 0 14px;
        display: flex; align-items: center; gap: 8px;
    }
    .cf-section-label:first-child { margin-top: 0; }
    .cf-section-label::after { content: ''; flex: 1; height: 1px; background: #f3f4f6; }
    .cf-row  { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .cf-row3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }
    @media(max-width:640px){ .cf-row, .cf-row3 { grid-template-columns: 1fr; } }

    .cf-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 4px; }
    .cf-label { font-size: .78rem; font-weight: 600; color: #374151; }
    .cf-label .req { color: #ef4444; margin-left: 2px; }
    .cf-input {
        width: 100%; padding: 9px 13px; border: 1px solid #d1d5db; border-radius: 9px;
        font-size: .87rem; color: #111827; background: #fff;
        transition: border-color .15s, box-shadow .15s; outline: none;
    }
    .cf-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.12); }
    .cf-input.is-invalid { border-color: #ef4444; }
    .cf-error { font-size: .75rem; color: #ef4444; margin-top: 2px; }
    .cf-hint  { font-size: .73rem; color: #9ca3af; margin-top: 2px; }

    .cf-toggle-row { display: flex; align-items: center; gap: 12px; }
    .cf-toggle { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
    .cf-toggle input { opacity: 0; width: 0; height: 0; }
    .cf-toggle-slider {
        position: absolute; inset: 0; background: #d1d5db; border-radius: 24px; cursor: pointer;
        transition: background .2s;
    }
    .cf-toggle-slider::before {
        content: ''; position: absolute; left: 3px; top: 3px;
        width: 18px; height: 18px; background: #fff; border-radius: 50%;
        transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .cf-toggle input:checked + .cf-toggle-slider { background: #7c3aed; }
    .cf-toggle input:checked + .cf-toggle-slider::before { transform: translateX(20px); }

    .cf-footer {
        padding: 20px 28px; border-top: 1px solid #f3f4f6;
        display: flex; align-items: center; gap: 12px;
    }
    .cf-btn-save {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 22px; border-radius: 9px; border: none; cursor: pointer;
        font-size: .88rem; font-weight: 600; color: #fff;
        background: linear-gradient(135deg, #7c3aed, #5b21b6);
        transition: opacity .15s, transform .12s;
    }
    .cf-btn-save:hover { opacity: .9; transform: translateY(-1px); }
    .cf-btn-cancel {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 18px; border-radius: 9px; border: 1px solid #e5e7eb; cursor: pointer;
        font-size: .88rem; font-weight: 600; color: #6b7280; background: #fff;
        text-decoration: none; transition: background .15s;
    }
    .cf-btn-cancel:hover { background: #f9fafb; text-decoration: none; color: #374151; }

    .cf-allergy-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap: 10px;
        margin-top: 10px;
    }
    .cf-allergy-item {
        display: flex; align-items: center; gap: 8px; padding: 9px 12px;
        border: 1px solid #e5e7eb; border-radius: 9px; cursor: pointer;
        transition: border-color .15s, background .15s; font-size: .83rem; color: #374151;
    }
    .cf-allergy-item:has(input:checked) {
        border-color: #7c3aed; background: #f5f3ff; color: #5b21b6;
    }
    .cf-allergy-item input { accent-color: #7c3aed; width: 15px; height: 15px; }

    /* roles multi-select */
    .cf-input[multiple] { height: auto; min-height: 110px; padding: 6px; }
    .cf-input[multiple] option { padding: 5px 8px; border-radius: 5px; }
    .cf-select-actions { display: flex; gap: 6px; margin-bottom: 6px; }
    .cf-select-btn {
        font-size: .72rem; font-weight: 600; padding: 3px 10px; border-radius: 6px;
        border: 1px solid #e5e7eb; background: #f3f4f6; color: #374151; cursor: pointer;
    }
    .cf-select-btn:hover { background: #e5e7eb; }
</style>

<div class="cf-card">
    {{-- Header --}}
    <div class="cf-header">
        <div class="cf-header-icon"><i class="fas fa-user-edit"></i></div>
        <div>
            <div class="cf-header-title">Edit {{ $isCustomer ? 'Customer' : 'User' }}</div>
            <div class="cf-header-sub">{{ $user->name ?? 'Unknown' }} · #{{ $user->id }}</div>
        </div>
    </div>

    {{-- Validation errors --}}
    @if($errors->any())
    <div style="margin:20px 28px 0;padding:12px 16px;border:1px solid #fecaca;background:#fee2e2;border-radius:10px;font-size:.83rem;color:#dc2626;">
        <strong><i class="fas fa-exclamation-circle mr-1"></i> Please fix the following:</strong>
        <ul style="margin:6px 0 0 18px;padding:0;">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="cf-body">

        @if($isCustomer)
            {{-- ── CUSTOMER FIELDS ── --}}

            {{-- Contact --}}
            <div class="cf-section-label"><i class="fas fa-phone" style="color:#7c3aed;font-size:.8rem;"></i> Contact Info</div>
            <div class="cf-row3">
                <div class="cf-group">
                    <label class="cf-label">Country Code <span class="req">*</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.85rem;">+</span>
                        <input type="text" name="country_code" inputmode="numeric"
                            class="cf-input {{ $errors->has('country_code') ? 'is-invalid' : '' }}"
                            placeholder="965" value="{{ old('country_code', $user->country_code) }}"
                            style="padding-left:24px;">
                    </div>
                    <span class="cf-hint">e.g. 965 for Kuwait</span>
                    @if($errors->has('country_code'))<span class="cf-error">{{ $errors->first('country_code') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Mobile Number <span class="req">*</span></label>
                    <input type="text" name="mobile" inputmode="numeric"
                        class="cf-input {{ $errors->has('mobile') ? 'is-invalid' : '' }}"
                        placeholder="65560520" value="{{ old('mobile', $user->mobile) }}">
                    <span class="cf-hint">Local number only — no country code</span>
                    @if($errors->has('mobile'))<span class="cf-error">{{ $errors->first('mobile') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Full Name <span class="req">*</span></label>
                    <input type="text" name="name"
                        class="cf-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Customer full name" value="{{ old('name', $user->name) }}">
                    @if($errors->has('name'))<span class="cf-error">{{ $errors->first('name') }}</span>@endif
                </div>
            </div>

            {{-- Personal --}}
            <div class="cf-section-label"><i class="fas fa-id-card" style="color:#0891b2;font-size:.8rem;"></i> Personal Info</div>
            <div class="cf-row3">
                <div class="cf-group">
                    <label class="cf-label">Date of Birth <span class="req">*</span></label>
                    <input type="date" name="dob"
                        class="cf-input {{ $errors->has('dob') ? 'is-invalid' : '' }}"
                        value="{{ old('dob', $user->dob) }}">
                    @if($errors->has('dob'))<span class="cf-error">{{ $errors->first('dob') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Gender <span class="req">*</span></label>
                    <select name="gender" class="cf-input {{ $errors->has('gender') ? 'is-invalid' : '' }}">
                        <option value="">— Select —</option>
                        <option value="male"   {{ old('gender', $user->gender) === 'male'   ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other"  {{ old('gender', $user->gender) === 'other'  ? 'selected' : '' }}>Other</option>
                    </select>
                    @if($errors->has('gender'))<span class="cf-error">{{ $errors->first('gender') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Affiliated Code</label>
                    <input type="text" name="affiliated_code_text"
                        class="cf-input {{ $errors->has('affiliated_code_text') ? 'is-invalid' : '' }}"
                        placeholder="e.g. PROMO10"
                        value="{{ old('affiliated_code_text', $user->affiliatedCode->code ?? '') }}">
                    <span class="cf-hint">Optional — referral / promo code</span>
                    @if($errors->has('affiliated_code_text'))<span class="cf-error">{{ $errors->first('affiliated_code_text') }}</span>@endif
                </div>
            </div>

            {{-- Body --}}
            <div class="cf-section-label"><i class="fas fa-weight" style="color:#0891b2;font-size:.8rem;"></i> Body Measurements</div>
            <div class="cf-row">
                <div class="cf-group">
                    <label class="cf-label">Height (cm) <span class="req">*</span></label>
                    <input type="number" step="0.1" name="height"
                        class="cf-input {{ $errors->has('height') ? 'is-invalid' : '' }}"
                        placeholder="e.g. 175" value="{{ old('height', $user->height) }}">
                    @if($errors->has('height'))<span class="cf-error">{{ $errors->first('height') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Weight (kg) <span class="req">*</span></label>
                    <input type="number" step="0.1" name="weight"
                        class="cf-input {{ $errors->has('weight') ? 'is-invalid' : '' }}"
                        placeholder="e.g. 70" value="{{ old('weight', $user->weight) }}">
                    @if($errors->has('weight'))<span class="cf-error">{{ $errors->first('weight') }}</span>@endif
                </div>
            </div>

            {{-- Goals --}}
            <div class="cf-section-label"><i class="fas fa-bullseye" style="color:#16a34a;font-size:.8rem;"></i> Goals &amp; Activity</div>
            <div class="cf-row">
                <div class="cf-group">
                    <label class="cf-label">Goal <span class="req">*</span></label>
                    <select name="goal" class="cf-input {{ $errors->has('goal') ? 'is-invalid' : '' }}">
                        <option value="">— Select goal —</option>
                        <option value="eat_healthy"     {{ old('goal', $user->goal) === 'eat_healthy'     ? 'selected' : '' }}>Eat Healthy</option>
                        <option value="lose_weight"     {{ old('goal', $user->goal) === 'lose_weight'     ? 'selected' : '' }}>Lose Weight</option>
                        <option value="gain_weight"     {{ old('goal', $user->goal) === 'gain_weight'     ? 'selected' : '' }}>Gain Weight</option>
                        <option value="build_muscle"    {{ old('goal', $user->goal) === 'build_muscle'    ? 'selected' : '' }}>Build Muscle</option>
                        <option value="maintain_weight" {{ old('goal', $user->goal) === 'maintain_weight' ? 'selected' : '' }}>Maintain Weight</option>
                    </select>
                    @if($errors->has('goal'))<span class="cf-error">{{ $errors->first('goal') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Activity Level <span class="req">*</span></label>
                    <select name="activity_level" class="cf-input {{ $errors->has('activity_level') ? 'is-invalid' : '' }}">
                        <option value="">— Select level —</option>
                        <option value="sedentary"      {{ old('activity_level', $user->activity_level) === 'sedentary'      ? 'selected' : '' }}>Sedentary</option>
                        <option value="lightly_active" {{ old('activity_level', $user->activity_level) === 'lightly_active' ? 'selected' : '' }}>Lightly Active</option>
                        <option value="very_active"    {{ old('activity_level', $user->activity_level) === 'very_active'    ? 'selected' : '' }}>Very Active</option>
                        <option value="highly_active"  {{ old('activity_level', $user->activity_level) === 'highly_active'  ? 'selected' : '' }}>Highly Active</option>
                    </select>
                    @if($errors->has('activity_level'))<span class="cf-error">{{ $errors->first('activity_level') }}</span>@endif
                </div>
            </div>

            {{-- Dietary --}}
            <div class="cf-section-label"><i class="fas fa-allergies" style="color:#dc2626;font-size:.8rem;"></i> Dietary Preferences</div>

            @php $hasAllergiesOld = old('has_food_allergies', $user->has_food_allergies); @endphp
            <div class="cf-toggle-row" style="margin-bottom:14px;">
                <label class="cf-toggle">
                    <input type="checkbox" name="has_food_allergies" id="hasAllergies" value="1"
                        {{ $hasAllergiesOld ? 'checked' : '' }}>
                    <span class="cf-toggle-slider"></span>
                </label>
                <div>
                    <div style="font-size:.87rem;font-weight:600;color:#111827;">Has Food Allergies</div>
                    <div style="font-size:.75rem;color:#9ca3af;">Enable to select specific allergens</div>
                </div>
            </div>

            <div id="allergiesSection" style="{{ $hasAllergiesOld ? '' : 'display:none;' }}">
                <label class="cf-label" style="margin-bottom:4px;">Select Allergies</label>
                @if($errors->has('allergies'))<span class="cf-error" style="display:block;margin-bottom:8px;">{{ $errors->first('allergies') }}</span>@endif
                @php
                    $allergyOptions = ['Gluten','Dairy','Eggs','Nuts','Peanuts','Shellfish','Fish','Soy','Wheat','Sesame','Lactose','Tree Nuts'];
                    $currentAllergies = old('allergies', $user->allergies ?? []);
                    $currentAllergies = array_map('strtolower', $currentAllergies);
                @endphp
                <div class="cf-allergy-grid">
                    @foreach($allergyOptions as $allergen)
                    <label class="cf-allergy-item">
                        <input type="checkbox" name="allergies[]" value="{{ strtolower($allergen) }}"
                            {{ in_array(strtolower($allergen), $currentAllergies) ? 'checked' : '' }}>
                        {{ $allergen }}
                    </label>
                    @endforeach
                </div>
                <div style="margin-top:10px;">
                    <label class="cf-label">Other Allergy (optional)</label>
                    <input type="text" name="allergy_other" class="cf-input" placeholder="Type any other allergy..."
                        value="{{ old('allergy_other') }}" style="max-width:320px;">
                    <span class="cf-hint">Will be added to the list if filled</span>
                </div>
            </div>

        @else
            {{-- ── ADMIN / STAFF FIELDS ── --}}

            {{-- Account --}}
            <div class="cf-section-label"><i class="fas fa-user-shield" style="color:#7c3aed;font-size:.8rem;"></i> Account Info</div>
            <div class="cf-row">
                <div class="cf-group">
                    <label class="cf-label">Full Name <span class="req">*</span></label>
                    <input type="text" name="name"
                        class="cf-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Full name" value="{{ old('name', $user->name) }}" required>
                    @if($errors->has('name'))<span class="cf-error">{{ $errors->first('name') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Email Address <span class="req">*</span></label>
                    <input type="email" name="email"
                        class="cf-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        placeholder="admin@example.com" value="{{ old('email', $user->email) }}" required>
                    @if($errors->has('email'))<span class="cf-error">{{ $errors->first('email') }}</span>@endif
                </div>
            </div>

            {{-- Password --}}
            <div class="cf-section-label"><i class="fas fa-lock" style="color:#9ca3af;font-size:.8rem;"></i> Password</div>
            <div class="cf-row">
                <div class="cf-group">
                    <label class="cf-label">New Password</label>
                    <input type="password" name="password"
                        class="cf-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="Leave blank to keep current">
                    <span class="cf-hint">Only fill this if you want to change the password</span>
                    @if($errors->has('password'))<span class="cf-error">{{ $errors->first('password') }}</span>@endif
                </div>
            </div>

            {{-- Permissions --}}
            <div class="cf-section-label"><i class="fas fa-key" style="color:#d97706;font-size:.8rem;"></i> Permissions</div>
            <div class="cf-group" style="margin-bottom:16px;">
                <label class="cf-label">Roles <span class="req">*</span></label>
                <div class="cf-select-actions">
                    <button type="button" class="cf-select-btn" id="selectAll">Select All</button>
                    <button type="button" class="cf-select-btn" id="deselectAll">Deselect All</button>
                </div>
                <select class="cf-input select2 {{ $errors->has('roles') ? 'is-invalid' : '' }}"
                        name="roles[]" id="roles" multiple required>
                    @foreach($roles as $id => $role)
                        <option value="{{ $id }}"
                            {{ (in_array($id, old('roles', [])) || $user->roles->contains($id)) ? 'selected' : '' }}>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('roles'))<span class="cf-error">{{ $errors->first('roles') }}</span>@endif
            </div>

            @if(auth()->user()->is_admin)
            <div class="cf-group">
                <label class="cf-label">Branch Assignment</label>
                <select class="cf-input select2 {{ $errors->has('branch_id') ? 'is-invalid' : '' }}"
                        name="branch_id" id="branch_id">
                    <option value="">— No branch (full admin access) —</option>
                    @foreach($branches as $id => $name)
                        <option value="{{ $id }}"
                            {{ (old('branch_id', $user->branch_id) == $id) ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <span class="cf-hint">
                    @if($user->branch_id)
                        Currently assigned to <strong>{{ $user->branch->name ?? 'unknown' }}</strong>.
                    @else
                        Assign a branch to restrict this user to that branch's data only.
                    @endif
                </span>
                @if($errors->has('branch_id'))<span class="cf-error">{{ $errors->first('branch_id') }}</span>@endif
            </div>
            @endif
        @endif

        </div>

        <div class="cf-footer">
            <button type="submit" class="cf-btn-save">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="{{ route('admin.users.index') }}" class="cf-btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

@endsection
@section('scripts')
<script>
@if($isCustomer)
document.getElementById('hasAllergies').addEventListener('change', function () {
    document.getElementById('allergiesSection').style.display = this.checked ? '' : 'none';
});
@else
document.getElementById('selectAll').addEventListener('click', function () {
    document.querySelectorAll('#roles option').forEach(o => o.selected = true);
});
document.getElementById('deselectAll').addEventListener('click', function () {
    document.querySelectorAll('#roles option').forEach(o => o.selected = false);
});
@endif
</script>
@endsection
