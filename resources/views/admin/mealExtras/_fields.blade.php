@php $me = $mealExtra ?? null; @endphp
<div class="mf-grid-2">
    <div class="mf-field">
        <label class="mf-label" for="name">Extra Name</label>
        <input class="mf-input {{ $errors->has('name') ? 'is-err' : '' }}"
               type="text" name="name" id="name"
               value="{{ old('name', $me->name ?? '') }}" placeholder="e.g. Bread" required>
        @if($errors->has('name'))
            <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('name') }}</div>
        @endif
    </div>
    <div class="mf-field">
        <label class="mf-label" for="name_ar">Extra Name (Arabic)</label>
        <input class="mf-input {{ $errors->has('name_ar') ? 'is-err' : '' }}"
               type="text" name="name_ar" id="name_ar" dir="rtl"
               value="{{ old('name_ar', $me->name_ar ?? '') }}" placeholder="مثال: خبز">
        @if($errors->has('name_ar'))
            <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('name_ar') }}</div>
        @endif
    </div>
</div>

<div class="mf-grid-2">
    <div class="mf-field">
        <label class="mf-label" for="selection_type">Selection Type</label>
        <select class="mf-input" name="selection_type" id="selection_type">
            @foreach(App\Models\MealExtra::SELECTION_TYPES as $key => $label)
                <option value="{{ $key }}" {{ old('selection_type', $me->selection_type ?? 'single') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div style="font-size:.72rem;color:#9ca3af;margin-top:4px;">
            Single = user picks one option · Multiple = user can pick several.
        </div>
    </div>
    <div class="mf-field">
        <label class="mf-label" for="sort_order">Sort Order</label>
        <input class="mf-input" type="number" name="sort_order" id="sort_order" min="0"
               value="{{ old('sort_order', $me->sort_order ?? 0) }}" placeholder="0">
    </div>
</div>

<div class="mf-grid-2">
    <div>
        <label class="mf-label">Required</label>
        <input type="hidden" name="is_required" value="0">
        <div class="toggle-wrap" style="margin-top:4px;">
            <label class="toggle">
                <input type="checkbox" name="is_required" value="1"
                       {{ old('is_required', $me->is_required ?? false) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
            <span class="toggle-label">User must choose an option</span>
        </div>
    </div>
    <div>
        <label class="mf-label">Status</label>
        <input type="hidden" name="is_active" value="0">
        <div class="toggle-wrap" style="margin-top:4px;">
            <label class="toggle">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $me->is_active ?? true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
            <span class="toggle-label">Active</span>
        </div>
    </div>
</div>
