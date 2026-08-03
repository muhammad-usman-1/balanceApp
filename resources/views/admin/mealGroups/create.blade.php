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
.mf-input:focus { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.08); }

.mg-picker { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
.mg-picker-toolbar {
    display: flex; align-items: center; gap: 8px; padding: 10px 12px;
    background: #f9fafb; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap;
}
.mg-picker-search { position: relative; flex: 1; min-width: 180px; }
.mg-picker-search input {
    width: 100%; padding: 7px 10px 7px 30px; border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: .82rem; outline: none;
}
.mg-picker-search .fa-search { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: .72rem; }
.mg-picker-btn {
    padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff;
    font-size: .78rem; font-weight: 600; color: #374151; cursor: pointer; white-space: nowrap;
}
.mg-picker-btn:hover { background: #f3f4f6; }
.mg-picker-count {
    font-size: .78rem; font-weight: 700; color: #dc2626; white-space: nowrap; margin-left: auto;
}
.mg-picker-list { max-height: 380px; overflow-y: auto; }
.mg-picker-item {
    display: flex; align-items: center; gap: 10px; padding: 9px 14px;
    border-bottom: 1px solid #f3f4f6; cursor: pointer;
}
.mg-picker-item:last-child { border-bottom: none; }
.mg-picker-item:hover { background: #fafafa; }
.mg-picker-item.is-checked { background: #fef2f2; }
.mg-picker-item input[type=checkbox] { width: 16px; height: 16px; accent-color: #dc2626; cursor: pointer; flex-shrink: 0; }
.mg-picker-thumb { width: 32px; height: 32px; border-radius: 7px; object-fit: cover; border: 1px solid #e5e7eb; flex-shrink: 0; }
.mg-picker-thumb-ph {
    width: 32px; height: 32px; border-radius: 7px; flex-shrink: 0;
    background: linear-gradient(135deg,#e0e7ff,#c7d2fe);
    display: inline-flex; align-items: center; justify-content: center; color: #6366f1; font-size: .72rem;
}
.mg-picker-name { font-size: .84rem; font-weight: 500; color: #111827; }
.mg-picker-cat { font-size: .72rem; color: #9ca3af; }
.mg-picker-empty { padding: 30px 16px; text-align: center; color: #9ca3af; font-size: .84rem; }
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle mr-2" style="color:#22c55e;"></i> Add Meal Group</h3>
        <a href="{{ route('admin.meal-groups.index') }}" class="idx-btn ib-gray">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="idx-flash idx-flash-error" style="margin:16px 20px 0;">
        <i class="fas fa-exclamation-circle"></i> Please fix the errors below before saving.
    </div>
    @endif

    <div class="card-body" style="padding:24px !important;">
        <form method="POST" action="{{ route('admin.meal-groups.store') }}">
            @csrf

            <div style="display:grid; grid-template-columns: 1fr 200px; gap:14px; margin-bottom:20px;">
                <div>
                    <label class="mf-label" for="name">Group Name</label>
                    <input type="text" name="name" id="name" class="mf-input @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="e.g. Premium Proteins" required>
                    @error('name')<div class="mf-err" style="color:#ef4444;font-size:.75rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="mf-label" for="weekly_limit">Weekly Limit</label>
                    <input type="number" name="weekly_limit" id="weekly_limit" min="1" max="7"
                           class="mf-input @error('weekly_limit') is-invalid @enderror"
                           value="{{ old('weekly_limit', 2) }}" required>
                    @error('weekly_limit')<div class="mf-err" style="color:#ef4444;font-size:.75rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
            </div>

            <label class="mf-label">Meals in this Group</label>
            <div class="mg-picker">
                <div class="mg-picker-toolbar">
                    <div class="mg-picker-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="mgMealSearch" placeholder="Filter meals by name or category…">
                    </div>
                    <button type="button" class="mg-picker-btn" id="mgSelectAll">Select all visible</button>
                    <button type="button" class="mg-picker-btn" id="mgClearAll">Clear all</button>
                    <span class="mg-picker-count"><span id="mgSelectedCount">0</span> selected</span>
                </div>
                <div class="mg-picker-list" id="mgPickerList">
                    @forelse($meals as $meal)
                        @php $thumb = $meal->getFirstMedia('image'); @endphp
                        <label class="mg-picker-item" data-search="{{ strtolower($meal->title.' '.($meal->category->name ?? '')) }}">
                            <input type="checkbox" name="meal_ids[]" value="{{ $meal->id }}"
                                   {{ in_array($meal->id, old('meal_ids', [])) ? 'checked' : '' }}>
                            @if($thumb)
                                <img src="{{ $thumb->getUrl('thumb') ?: $thumb->getUrl() }}" class="mg-picker-thumb" alt="">
                            @else
                                <div class="mg-picker-thumb-ph"><i class="fas fa-utensils"></i></div>
                            @endif
                            <div>
                                <div class="mg-picker-name">{{ $meal->title }}</div>
                                @if($meal->category?->name)
                                    <div class="mg-picker-cat">{{ $meal->category->name }}</div>
                                @endif
                            </div>
                        </label>
                    @empty
                        <div class="mg-picker-empty">
                            No available meals. All active meals already belong to a group.
                        </div>
                    @endforelse
                </div>
            </div>
            <small class="form-text text-muted">Only meals not already in another group are shown.</small>

            <div style="margin-top:24px; padding-top:20px; border-top:1px solid #f3f4f6; display:flex; gap:10px;">
                <button type="submit" class="idx-btn ib-green" style="padding:9px 22px; font-size:.85rem;">
                    <i class="fas fa-check"></i> Save Group
                </button>
                <a href="{{ route('admin.meal-groups.index') }}" class="idx-btn ib-gray" style="padding:9px 18px; font-size:.85rem;">
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
    var list       = document.getElementById('mgPickerList');
    var items      = Array.prototype.slice.call(list.querySelectorAll('.mg-picker-item'));
    var search     = document.getElementById('mgMealSearch');
    var selectAll  = document.getElementById('mgSelectAll');
    var clearAll   = document.getElementById('mgClearAll');
    var countLabel = document.getElementById('mgSelectedCount');

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
