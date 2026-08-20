@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
  .content-wrapper { background: #fff !important; }

.mf-label {
    font-size: .75rem; font-weight: 700; color: #374151;
    text-transform: uppercase; letter-spacing: .04em;
    display: block; margin-bottom: 6px;
}
.mf-input {
    width: 100%; padding: 9px 12px; font-size: .85rem; color: #111827;
    border: 1px solid #e5e7eb; border-radius: 8px; background: #fff;
    outline: none; transition: border-color .15s, box-shadow .15s;
    box-sizing: border-box;
}
.mf-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.mf-input.is-err { border-color: #ef4444; }
.mf-err  { font-size: .75rem; color: #ef4444; margin-top: 4px; }
.mf-hint { font-size: .73rem; color: #9ca3af; margin-top: 4px; }
.mf-section {
    font-size: .7rem; font-weight: 800; color: #9ca3af; text-transform: uppercase;
    letter-spacing: .08em; margin: 0 0 14px; padding-bottom: 8px;
    border-bottom: 1px solid #f3f4f6;
}
.mf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.mf-field  { margin-bottom: 18px; }

/* Toggle */
.toggle-wrap { display: flex; align-items: center; gap: 10px; }
.toggle { position: relative; width: 42px; height: 24px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; inset: 0; background: #e5e7eb;
    border-radius: 24px; cursor: pointer; transition: background .2s;
}
.toggle-slider:before {
    content: ''; position: absolute;
    height: 18px; width: 18px; left: 3px; bottom: 3px;
    background: #fff; border-radius: 50%; transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.toggle input:checked + .toggle-slider { background: #22c55e; }
.toggle input:checked + .toggle-slider:before { transform: translateX(18px); }
.toggle-label { font-size: .84rem; color: #374151; font-weight: 500; }
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-pen mr-2" style="color:#b45309;"></i> Edit Subscription Plan</h3>
        <a href="{{ route('admin.subcrption-plans.index') }}" class="idx-btn ib-gray">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="idx-flash idx-flash-error" style="margin:16px 20px 0;">
        <i class="fas fa-exclamation-circle"></i> Please fix the errors below before saving.
    </div>
    @endif

    <div class="card-body" style="padding: 24px !important;">
        <form method="POST" action="{{ route('admin.subcrption-plans.update', [$subcrptionPlan->id]) }}">
            @method('PUT')
            @csrf

            {{-- Basic Info --}}
            <p class="mf-section"><i class="fas fa-info-circle mr-1"></i> Basic Info</p>

            <div class="mf-grid-2">
                <div class="mf-field">
                    <label class="mf-label" for="title">Plan Name</label>
                    <input class="mf-input {{ $errors->has('title') ? 'is-err' : '' }}"
                           type="text" name="title" id="title"
                           value="{{ old('title', $subcrptionPlan->title) }}" placeholder="e.g. Standard Plan">
                    @if($errors->has('title'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('title') }}</div>
                    @endif
                </div>
                <div class="mf-field">
                    <label class="mf-label" for="title_ar">Plan Name (Arabic)</label>
                    <input class="mf-input {{ $errors->has('title_ar') ? 'is-err' : '' }}"
                           type="text" name="title_ar" id="title_ar" dir="rtl"
                           value="{{ old('title_ar', $subcrptionPlan->title_ar) }}" placeholder="مثال: الخطة القياسية">
                    @if($errors->has('title_ar'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('title_ar') }}</div>
                    @endif
                </div>
            </div>

            <div class="mf-grid-2">
                <div class="mf-field">
                    <label class="mf-label" for="description">Description</label>
                    <textarea class="mf-input {{ $errors->has('description') ? 'is-err' : '' }}"
                              name="description" id="description"
                              rows="3" placeholder="Briefly describe what this plan includes…"
                              style="resize:vertical;">{{ old('description', $subcrptionPlan->description) }}</textarea>
                    @if($errors->has('description'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('description') }}</div>
                    @endif
                </div>
                <div class="mf-field">
                    <label class="mf-label" for="description_ar">Description (Arabic)</label>
                    <textarea class="mf-input {{ $errors->has('description_ar') ? 'is-err' : '' }}"
                              name="description_ar" id="description_ar" dir="rtl"
                              rows="3" placeholder="وصف مختصر لما تتضمنه هذه الخطة…"
                              style="resize:vertical;">{{ old('description_ar', $subcrptionPlan->description_ar) }}</textarea>
                    @if($errors->has('description_ar'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('description_ar') }}</div>
                    @endif
                </div>
            </div>

            {{-- Pricing & Duration --}}
            <p class="mf-section" style="margin-top:8px;"><i class="fas fa-tag mr-1"></i> Pricing & Duration</p>

            <div class="mf-grid-2" style="margin-bottom:18px;">
                <div>
                    <label class="mf-label" for="price">
                        <i class="fas fa-coins" style="color:#f59e0b;"></i> Price (KWD)
                    </label>
                    <input class="mf-input {{ $errors->has('price') ? 'is-err' : '' }}"
                           type="number" name="price" id="price"
                           value="{{ old('price', $subcrptionPlan->price) }}" step="0.001" placeholder="0.000">
                    @if($errors->has('price'))
                        <div class="mf-err">{{ $errors->first('price') }}</div>
                    @endif
                </div>
                <div>
                    <label class="mf-label" for="no_of_weeks">
                        <i class="fas fa-calendar-week" style="color:#6366f1;"></i> Duration (weeks)
                    </label>
                    <input class="mf-input {{ $errors->has('no_of_weeks') ? 'is-err' : '' }}"
                           type="number" name="no_of_weeks" id="no_of_weeks"
                           value="{{ old('no_of_weeks', $subcrptionPlan->no_of_weeks) }}" min="1" step="1" placeholder="e.g. 4">
                    @if($errors->has('no_of_weeks'))
                        <div class="mf-err">{{ $errors->first('no_of_weeks') }}</div>
                    @endif
                </div>
            </div>

            {{-- Meal Configuration --}}
            <p class="mf-section"><i class="fas fa-utensils mr-1"></i> Meal Configuration</p>

            <div class="mf-grid-2" style="margin-bottom:18px;">
                <div>
                    <label class="mf-label" for="meal_count">
                        <i class="fas fa-utensils" style="color:#ea580c;"></i> Meals per day
                    </label>
                    <input class="mf-input {{ $errors->has('meal_count') ? 'is-err' : '' }}"
                           type="number" name="meal_count" id="meal_count"
                           value="{{ old('meal_count', $subcrptionPlan->meal_count) }}" step="1" min="0" placeholder="e.g. 3">
                    @if($errors->has('meal_count'))
                        <div class="mf-err">{{ $errors->first('meal_count') }}</div>
                    @endif
                </div>
                <div>
                    <label class="mf-label" for="snack_count">
                        <i class="fas fa-cookie-bite" style="color:#d97706;"></i> Snacks per day
                    </label>
                    <input class="mf-input {{ $errors->has('snack_count') ? 'is-err' : '' }}"
                           type="number" name="snack_count" id="snack_count"
                           value="{{ old('snack_count', $subcrptionPlan->snack_count) }}" step="1" min="0" placeholder="e.g. 1">
                    @if($errors->has('snack_count'))
                        <div class="mf-err">{{ $errors->first('snack_count') }}</div>
                    @endif
                </div>
            </div>

            {{-- Days Range --}}
            <p class="mf-section"><i class="fas fa-calendar-alt mr-1"></i> Delivery Days</p>

            <div class="mf-grid-2" style="margin-bottom:18px;">
                <div>
                    <label class="mf-label" for="min_days">
                        <i class="fas fa-arrow-down" style="color:#0284c7;"></i> Min days / week
                    </label>
                    <input class="mf-input {{ $errors->has('min_days') ? 'is-err' : '' }}"
                           type="number" name="min_days" id="min_days"
                           value="{{ old('min_days', $subcrptionPlan->min_days) }}" min="1" max="6" step="1" placeholder="1–6">
                    @if($errors->has('min_days'))
                        <div class="mf-err">{{ $errors->first('min_days') }}</div>
                    @endif
                    <div class="mf-hint">Minimum days the user must select per week.</div>
                </div>
                <div>
                    <label class="mf-label" for="max_days">
                        <i class="fas fa-arrow-up" style="color:#0284c7;"></i> Max days / week
                    </label>
                    <input class="mf-input {{ $errors->has('max_days') ? 'is-err' : '' }}"
                           type="number" name="max_days" id="max_days"
                           value="{{ old('max_days', $subcrptionPlan->max_days) }}" min="1" max="7" step="1" placeholder="1–7">
                    @if($errors->has('max_days'))
                        <div class="mf-err">{{ $errors->first('max_days') }}</div>
                    @endif
                    <div class="mf-hint">Maximum days the user can select per week.</div>
                </div>
            </div>

            {{-- Status --}}
            <p class="mf-section"><i class="fas fa-sliders-h mr-1"></i> Settings</p>

            <div class="mf-field">
                <label class="mf-label">Status</label>
                <input type="hidden" name="is_active" value="0">
                <div class="toggle-wrap">
                    <label class="toggle">
                        <input type="checkbox" name="is_active" id="is_active"
                               value="1" {{ old('is_active', $subcrptionPlan->is_active) == 1 ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label" id="toggle-text">
                        {{ old('is_active', $subcrptionPlan->is_active) == 1 ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #f3f4f6;
                        display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="idx-btn ib-edit" style="padding: 9px 22px; font-size: .85rem;">
                    <i class="fas fa-check"></i> Save Changes
                </button>
                <a href="{{ route('admin.subcrption-plans.index') }}" class="idx-btn ib-gray" style="padding: 9px 18px; font-size: .85rem;">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@endsection
@section('scripts')
@parent
<script>
document.getElementById('is_active').addEventListener('change', function() {
    document.getElementById('toggle-text').textContent = this.checked ? 'Active' : 'Inactive';
});
</script>
@endsection
