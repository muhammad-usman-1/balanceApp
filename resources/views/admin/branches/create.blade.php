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
.mf-input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.mf-input.is-err { border-color: #ef4444; }
.mf-err { font-size: .75rem; color: #ef4444; margin-top: 4px; }
.mf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.mf-field { margin-bottom: 16px; }

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

/* Area picker */
.br-picker { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
.br-picker-toolbar {
    display: flex; align-items: center; gap: 8px; padding: 10px 12px;
    background: #f9fafb; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap;
}
.br-picker-search { position: relative; flex: 1; min-width: 180px; }
.br-picker-search input {
    width: 100%; padding: 7px 10px 7px 30px; border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: .82rem; outline: none;
}
.br-picker-search .fa-search { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: .72rem; }
.br-picker-btn {
    padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff;
    font-size: .78rem; font-weight: 600; color: #374151; cursor: pointer; white-space: nowrap;
}
.br-picker-btn:hover { background: #f3f4f6; }
.br-picker-count { font-size: .78rem; font-weight: 700; color: #0d9488; white-space: nowrap; margin-left: auto; }
.br-picker-list { max-height: 320px; overflow-y: auto; }
.br-picker-item {
    display: flex; align-items: center; gap: 10px; padding: 9px 14px;
    border-bottom: 1px solid #f3f4f6; cursor: pointer;
}
.br-picker-item:last-child { border-bottom: none; }
.br-picker-item:hover { background: #fafafa; }
.br-picker-item.is-checked { background: #f0fdfa; }
.br-picker-item input[type=checkbox] { width: 16px; height: 16px; accent-color: #0d9488; cursor: pointer; flex-shrink: 0; }
.br-picker-name { font-size: .84rem; font-weight: 500; color: #111827; }
.br-picker-sub { font-size: .72rem; color: #9ca3af; }
.br-picker-empty { padding: 30px 16px; text-align: center; color: #9ca3af; font-size: .84rem; }
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-code-branch mr-2" style="color:#0d9488;"></i> Add Branch</h3>
        <a href="{{ route('admin.branches.index') }}" class="idx-btn ib-gray">
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
        <form method="POST" action="{{ route('admin.branches.store') }}">
            @csrf

            <div class="mf-grid-2">
                <div class="mf-field">
                    <label class="mf-label" for="name">Branch Name</label>
                    <input class="mf-input {{ $errors->has('name') ? 'is-err' : '' }}"
                           type="text" name="name" id="name"
                           value="{{ old('name') }}" placeholder="e.g. Salmiya Branch" required>
                    @if($errors->has('name'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('name') }}</div>
                    @endif
                </div>
                <div class="mf-field">
                    <label class="mf-label" for="name_ar">Branch Name (Arabic)</label>
                    <input class="mf-input {{ $errors->has('name_ar') ? 'is-err' : '' }}"
                           type="text" name="name_ar" id="name_ar" dir="rtl"
                           value="{{ old('name_ar') }}" placeholder="مثال: فرع السالمية">
                    @if($errors->has('name_ar'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('name_ar') }}</div>
                    @endif
                </div>
            </div>

            <div class="mf-field">
                <label class="mf-label">Status</label>
                <input type="hidden" name="status" value="inactive">
                <div class="toggle-wrap" style="margin-top:4px;">
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

            <label class="mf-label">Associated Areas</label>
            <div class="br-picker">
                <div class="br-picker-toolbar">
                    <div class="br-picker-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="brAreaSearch" placeholder="Filter areas by name…">
                    </div>
                    <button type="button" class="br-picker-btn" id="brSelectAll">Select all visible</button>
                    <button type="button" class="br-picker-btn" id="brClearAll">Clear all</button>
                    <span class="br-picker-count"><span id="brSelectedCount">0</span> selected</span>
                </div>
                <div class="br-picker-list" id="brPickerList">
                    @forelse($areas as $area)
                        <label class="br-picker-item" data-search="{{ strtolower($area->name) }}">
                            <input type="checkbox" name="areas[]" value="{{ $area->id }}"
                                   {{ in_array($area->id, old('areas', [])) ? 'checked' : '' }}>
                            <div>
                                <div class="br-picker-name">{{ $area->name }}</div>
                                <div class="br-picker-sub">{{ number_format($area->delivery_charges, 2) }} KWD delivery</div>
                            </div>
                        </label>
                    @empty
                        <div class="br-picker-empty">No available areas. All active areas are already assigned to branches.</div>
                    @endforelse
                </div>
            </div>
            @if($errors->has('areas'))
                <div class="mf-err" style="margin-top:6px;"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('areas') }}</div>
            @endif
            <small class="form-text text-muted">Each area can only belong to one branch. Only unassigned areas are shown.</small>

            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #f3f4f6;
                        display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="idx-btn ib-green" style="padding: 9px 22px; font-size: .85rem;">
                    <i class="fas fa-check"></i> Save Branch
                </button>
                <a href="{{ route('admin.branches.index') }}" class="idx-btn ib-gray" style="padding: 9px 18px; font-size: .85rem;">
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
    var statusToggle = document.getElementById('status');
    var statusText   = document.getElementById('toggle-text');
    if (statusToggle) {
        statusToggle.addEventListener('change', function() {
            statusText.textContent = this.checked ? 'Active' : 'Inactive';
        });
    }

    var list       = document.getElementById('brPickerList');
    var items      = Array.prototype.slice.call(list.querySelectorAll('.br-picker-item'));
    var search     = document.getElementById('brAreaSearch');
    var selectAll  = document.getElementById('brSelectAll');
    var clearAll   = document.getElementById('brClearAll');
    var countLabel = document.getElementById('brSelectedCount');

    function updateCount() {
        var checked = items.filter(function(i) { return i.querySelector('input').checked; }).length;
        countLabel.textContent = checked;
    }

    function updateRowState(item) {
        item.classList.toggle('is-checked', item.querySelector('input').checked);
    }

    items.forEach(function(item) {
        updateRowState(item);
        item.querySelector('input').addEventListener('change', function() {
            updateRowState(item);
            updateCount();
        });
    });

    if (search) {
        search.addEventListener('input', function() {
            var term = this.value.trim().toLowerCase();
            items.forEach(function(item) {
                item.style.display = item.dataset.search.indexOf(term) !== -1 ? '' : 'none';
            });
        });
    }

    if (selectAll) {
        selectAll.addEventListener('click', function() {
            items.forEach(function(item) {
                if (item.style.display !== 'none') {
                    item.querySelector('input').checked = true;
                    updateRowState(item);
                }
            });
            updateCount();
        });
    }

    if (clearAll) {
        clearAll.addEventListener('click', function() {
            items.forEach(function(item) {
                item.querySelector('input').checked = false;
                updateRowState(item);
            });
            updateCount();
        });
    }

    updateCount();
})();
</script>
@endsection
