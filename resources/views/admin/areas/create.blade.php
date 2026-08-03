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
    outline: none; transition: border-color .15s, box-shadow .15s; box-sizing: border-box;
}
.mf-input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); }
.mf-input.is-err { border-color: #ef4444; }
.mf-err { font-size: .75rem; color: #ef4444; margin-top: 4px; }
.mf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.mf-field { margin-bottom: 16px; }
.mf-suffix { position: relative; }
.mf-suffix input { padding-right: 52px; }
.mf-suffix span {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    font-size: .72rem; color: #9ca3af; font-weight: 600;
}

/* Toggle switch */
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
        <h3><i class="fas fa-map-marker-alt mr-2" style="color:#059669;"></i> Add Area</h3>
        <a href="{{ route('admin.areas.index') }}" class="idx-btn ib-gray">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="idx-flash idx-flash-error" style="margin:16px 20px 0;">
        <i class="fas fa-exclamation-circle"></i>
        Please fix the errors below before saving.
    </div>
    @endif

    <div class="card-body" style="padding: 24px !important;">
        <form method="POST" action="{{ route('admin.areas.store') }}">
            @csrf

            <div class="mf-grid-2">
                <div class="mf-field">
                    <label class="mf-label" for="name">Area Name</label>
                    <input class="mf-input {{ $errors->has('name') ? 'is-err' : '' }}"
                           type="text" name="name" id="name"
                           value="{{ old('name') }}" placeholder="e.g. Salmiya" required>
                    @if($errors->has('name'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('name') }}</div>
                    @endif
                </div>
                <div class="mf-field">
                    <label class="mf-label" for="name_ar">Area Name (Arabic)</label>
                    <input class="mf-input {{ $errors->has('name_ar') ? 'is-err' : '' }}"
                           type="text" name="name_ar" id="name_ar" dir="rtl"
                           value="{{ old('name_ar') }}" placeholder="مثال: السالمية">
                    @if($errors->has('name_ar'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('name_ar') }}</div>
                    @endif
                </div>
            </div>

            <div class="mf-grid-2">
                <div class="mf-field">
                    <label class="mf-label" for="delivery_charges">Delivery Charges</label>
                    <div class="mf-suffix">
                        <input class="mf-input {{ $errors->has('delivery_charges') ? 'is-err' : '' }}"
                               type="number" name="delivery_charges" id="delivery_charges"
                               step="0.01" min="0" value="{{ old('delivery_charges', 0) }}" required>
                        <span>KWD</span>
                    </div>
                    @if($errors->has('delivery_charges'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('delivery_charges') }}</div>
                    @endif
                </div>
                <div class="mf-field">
                    <label class="mf-label">Status</label>
                    <input type="hidden" name="status" value="inactive">
                    <div class="toggle-wrap" style="margin-top:6px;">
                        <label class="toggle">
                            <input type="checkbox" name="status" id="status" value="active"
                                   {{ old('status', 'active') === 'active' ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label" id="toggle-text">
                            {{ old('status', 'active') === 'active' ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 8px; padding-top: 20px; border-top: 1px solid #f3f4f6;
                        display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="idx-btn ib-green" style="padding: 9px 22px; font-size: .85rem;">
                    <i class="fas fa-check"></i> Save Area
                </button>
                <a href="{{ route('admin.areas.index') }}" class="idx-btn ib-gray" style="padding: 9px 18px; font-size: .85rem;">
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
(function() {
    var toggle = document.getElementById('status');
    var toggleTxt = document.getElementById('toggle-text');
    if (toggle) {
        toggle.addEventListener('change', function() {
            toggleTxt.textContent = this.checked ? 'Active' : 'Inactive';
        });
    }
})();
</script>
@endsection
