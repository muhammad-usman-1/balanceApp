@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #f9fafb !important; }
    .cf-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
        box-shadow: 0 1px 6px rgba(0,0,0,.05); overflow: hidden; max-width: 820px; margin: 0 auto;
    }
    .cf-header {
        background: linear-gradient(135deg,#3b82f6,#2563eb);
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
    .cf-section-label::after {
        content: ''; flex: 1; height: 1px; background: #f3f4f6;
    }
    .cf-row  { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .cf-row3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }
    @media(max-width:640px){ .cf-row, .cf-row3 { grid-template-columns: 1fr; } }

    .cf-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 4px; }
    .cf-label {
        font-size: .78rem; font-weight: 600; color: #374151;
    }
    .cf-label .req { color: #ef4444; margin-left: 2px; }
    .cf-input {
        width: 100%; padding: 9px 13px; border: 1px solid #d1d5db; border-radius: 9px;
        font-size: .87rem; color: #111827; background: #fff;
        transition: border-color .15s, box-shadow .15s; outline: none;
    }
    .cf-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .cf-input.is-invalid { border-color: #ef4444; }
    .cf-error { font-size: .75rem; color: #ef4444; margin-top: 2px; }
    .cf-hint  { font-size: .73rem; color: #9ca3af; margin-top: 2px; }

    .cf-toggle-row { display: flex; align-items: center; gap: 12px; }
    .cf-toggle {
        position: relative; width: 44px; height: 24px; flex-shrink: 0;
    }
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
    .cf-toggle input:checked + .cf-toggle-slider { background: #3b82f6; }
    .cf-toggle input:checked + .cf-toggle-slider::before { transform: translateX(20px); }

    .cf-footer {
        padding: 20px 28px; border-top: 1px solid #f3f4f6;
        display: flex; align-items: center; gap: 12px;
    }
    .cf-btn-save {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 22px; border-radius: 9px; border: none; cursor: pointer;
        font-size: .88rem; font-weight: 600; color: #fff;
        background: linear-gradient(135deg,#3b82f6,#2563eb);
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
        display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px;
        margin-top: 10px;
    }
    .cf-allergy-item {
        display: flex; align-items: center; gap: 8px; padding: 9px 12px;
        border: 1px solid #e5e7eb; border-radius: 9px; cursor: pointer;
        transition: border-color .15s, background .15s; font-size: .83rem; color: #374151;
    }
    .cf-allergy-item:has(input:checked) {
        border-color: #3b82f6; background: #eff6ff; color: #1d4ed8;
    }
    .cf-allergy-item input { accent-color: #3b82f6; width: 15px; height: 15px; }
</style>

<div class="cf-card">
    {{-- Header --}}
    <div class="cf-header">
        <div class="cf-header-icon"><i class="fas fa-user-plus"></i></div>
        <div>
            <div class="cf-header-title">Add New Customer</div>
            <div class="cf-header-sub">Register a customer directly without OTP verification</div>
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

    <form method="POST" action="{{ route('admin.users.storeCustomer') }}" id="customerForm">
        @csrf
        <div class="cf-body">

            {{-- ── Contact ── --}}
            <div class="cf-section-label"><i class="fas fa-phone" style="color:#3b82f6;font-size:.8rem;"></i> Contact Info</div>
            <div class="cf-row3">
                <div class="cf-group">
                    <label class="cf-label">Country Code <span class="req">*</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.85rem;">+</span>
                        <input type="text" name="country_code" inputmode="numeric"
                            class="cf-input {{ $errors->has('country_code') ? 'is-invalid' : '' }}"
                            placeholder="965" value="{{ old('country_code', '965') }}"
                            style="padding-left:24px; max-width:100%;">
                    </div>
                    <span class="cf-hint">e.g. 965 for Kuwait</span>
                    @if($errors->has('country_code'))<span class="cf-error">{{ $errors->first('country_code') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Mobile Number <span class="req">*</span></label>
                    <input type="text" name="mobile" inputmode="numeric"
                        class="cf-input {{ $errors->has('mobile') ? 'is-invalid' : '' }}"
                        placeholder="65560520" value="{{ old('mobile') }}">
                    <span class="cf-hint">Local number only — no country code</span>
                    @if($errors->has('mobile'))<span class="cf-error">{{ $errors->first('mobile') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Full Name <span class="req">*</span></label>
                    <input type="text" name="name" class="cf-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Customer full name" value="{{ old('name') }}">
                    @if($errors->has('name'))<span class="cf-error">{{ $errors->first('name') }}</span>@endif
                </div>
            </div>

            {{-- ── Personal ── --}}
            <div class="cf-section-label"><i class="fas fa-id-card" style="color:#8b5cf6;font-size:.8rem;"></i> Personal Info</div>
            <div class="cf-row3">
                <div class="cf-group">
                    <label class="cf-label">Date of Birth <span class="req">*</span></label>
                    <input type="date" name="date_of_birth" class="cf-input {{ $errors->has('date_of_birth') ? 'is-invalid' : '' }}"
                        value="{{ old('date_of_birth') }}">
                    @if($errors->has('date_of_birth'))<span class="cf-error">{{ $errors->first('date_of_birth') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Gender <span class="req">*</span></label>
                    <select name="gender" class="cf-input {{ $errors->has('gender') ? 'is-invalid' : '' }}">
                        <option value="">— Select —</option>
                        <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Other</option>
                    </select>
                    @if($errors->has('gender'))<span class="cf-error">{{ $errors->first('gender') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Affiliated Code</label>
                    <input type="text" name="affiliated_code" class="cf-input {{ $errors->has('affiliated_code') ? 'is-invalid' : '' }}"
                        placeholder="e.g. PROMO10" value="{{ old('affiliated_code') }}">
                    <span class="cf-hint">Optional — referral / promo code</span>
                    @if($errors->has('affiliated_code'))<span class="cf-error">{{ $errors->first('affiliated_code') }}</span>@endif
                </div>
            </div>

            {{-- ── Body ── --}}
            <div class="cf-section-label"><i class="fas fa-weight" style="color:#0891b2;font-size:.8rem;"></i> Body Measurements</div>
            <div class="cf-row">
                <div class="cf-group">
                    <label class="cf-label">Height (cm) <span class="req">*</span></label>
                    <input type="number" step="0.1" name="height" class="cf-input {{ $errors->has('height') ? 'is-invalid' : '' }}"
                        placeholder="e.g. 175" value="{{ old('height') }}">
                    @if($errors->has('height'))<span class="cf-error">{{ $errors->first('height') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Weight (kg) <span class="req">*</span></label>
                    <input type="number" step="0.1" name="weight" class="cf-input {{ $errors->has('weight') ? 'is-invalid' : '' }}"
                        placeholder="e.g. 70" value="{{ old('weight') }}">
                    @if($errors->has('weight'))<span class="cf-error">{{ $errors->first('weight') }}</span>@endif
                </div>
            </div>

            {{-- ── Goals ── --}}
            <div class="cf-section-label"><i class="fas fa-bullseye" style="color:#16a34a;font-size:.8rem;"></i> Goals & Activity</div>
            <div class="cf-row">
                <div class="cf-group">
                    <label class="cf-label">Goal <span class="req">*</span></label>
                    <select name="goal" class="cf-input {{ $errors->has('goal') ? 'is-invalid' : '' }}">
                        <option value="">— Select goal —</option>
                        <option value="eat_healthy"     {{ old('goal') === 'eat_healthy'     ? 'selected' : '' }}>Eat Healthy</option>
                        <option value="lose_weight"     {{ old('goal') === 'lose_weight'     ? 'selected' : '' }}>Lose Weight</option>
                        <option value="gain_weight"     {{ old('goal') === 'gain_weight'     ? 'selected' : '' }}>Gain Weight</option>
                        <option value="build_muscle"    {{ old('goal') === 'build_muscle'    ? 'selected' : '' }}>Build Muscle</option>
                        <option value="maintain_weight" {{ old('goal') === 'maintain_weight' ? 'selected' : '' }}>Maintain Weight</option>
                    </select>
                    @if($errors->has('goal'))<span class="cf-error">{{ $errors->first('goal') }}</span>@endif
                </div>
                <div class="cf-group">
                    <label class="cf-label">Activity Level <span class="req">*</span></label>
                    <select name="activity_level" class="cf-input {{ $errors->has('activity_level') ? 'is-invalid' : '' }}">
                        <option value="">— Select level —</option>
                        <option value="sedentary"      {{ old('activity_level') === 'sedentary'      ? 'selected' : '' }}>Sedentary</option>
                        <option value="lightly_active" {{ old('activity_level') === 'lightly_active' ? 'selected' : '' }}>Lightly Active</option>
                        <option value="very_active"    {{ old('activity_level') === 'very_active'    ? 'selected' : '' }}>Very Active</option>
                        <option value="highly_active"  {{ old('activity_level') === 'highly_active'  ? 'selected' : '' }}>Highly Active</option>
                    </select>
                    @if($errors->has('activity_level'))<span class="cf-error">{{ $errors->first('activity_level') }}</span>@endif
                </div>
            </div>

            {{-- ── Dietary ── --}}
            <div class="cf-section-label"><i class="fas fa-allergies" style="color:#dc2626;font-size:.8rem;"></i> Dietary Preferences</div>

            <div class="cf-toggle-row" style="margin-bottom: 14px;">
                <label class="cf-toggle">
                    <input type="checkbox" name="has_food_allergies" id="hasAllergies" value="1"
                        {{ old('has_food_allergies') ? 'checked' : '' }}>
                    <span class="cf-toggle-slider"></span>
                </label>
                <div>
                    <div style="font-size:.87rem;font-weight:600;color:#111827;">Has Food Allergies</div>
                    <div style="font-size:.75rem;color:#9ca3af;">Enable to select specific allergens</div>
                </div>
            </div>

            <div id="allergiesSection" style="{{ old('has_food_allergies') ? '' : 'display:none;' }}">
                <label class="cf-label" style="margin-bottom:4px;">Select Allergies <span class="req">*</span></label>
                @if($errors->has('allergies'))<span class="cf-error" style="display:block;margin-bottom:8px;">{{ $errors->first('allergies') }}</span>@endif
                <div class="cf-allergy-grid">
                    @php
                    $allergyOptions = ['Gluten','Dairy','Eggs','Nuts','Peanuts','Shellfish','Fish','Soy','Wheat','Sesame','Lactose','Tree Nuts'];
                    $oldAllergies = old('allergies', []);
                    @endphp
                    @foreach($allergyOptions as $allergen)
                    <label class="cf-allergy-item">
                        <input type="checkbox" name="allergies[]" value="{{ strtolower($allergen) }}"
                            {{ in_array(strtolower($allergen), $oldAllergies) ? 'checked' : '' }}>
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

        </div>

        <div class="cf-footer">
            <button type="submit" class="cf-btn-save">
                <i class="fas fa-user-check"></i> Register Customer
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
document.getElementById('hasAllergies').addEventListener('change', function () {
    document.getElementById('allergiesSection').style.display = this.checked ? '' : 'none';
});
</script>
@endsection
