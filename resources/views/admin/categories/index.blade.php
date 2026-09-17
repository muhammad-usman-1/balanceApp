@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }

    .cat-preview-bar {
        display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        padding: 10px 14px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px;
        margin-bottom: 14px; font-size: .82rem; color: #3730a3;
    }
    .cat-preview-bar .cat-preview-label { font-weight: 700; flex-shrink: 0; white-space: nowrap; }
    .cat-preview-list { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .cat-preview-chip {
        font-weight: 700; background: #fff; border: 1px solid #c7d2fe; border-radius: 20px;
        padding: 2px 10px; color: #3730a3;
    }
    .cat-preview-list i.fa-arrow-right { color: #a5b4fc; font-size: .7rem; }

    .cat-order-num {
        width: 26px; height: 26px; border-radius: 50%; background: #eef2ff; color: #4338ca;
        font-weight: 700; font-size: .78rem; display: flex; align-items: center; justify-content: center;
    }
    .cat-draggable-row { cursor: grab; }
    .cat-draggable-row:active { cursor: grabbing; }
    .cat-draggable-row:hover { background: #f9fafb; }
    .cat-draggable-row.cat-dragging { opacity: .4; background: #eef2ff; }
</style>
<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-tags mr-2" style="color:#7c3aed;"></i> Categories</h3>
        <a href="{{ route('admin.categories.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="idx-flash idx-flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="card-body">
        <p style="font-size:.82rem;color:#6b7280;margin:0 0 10px;">
            <i class="fas fa-info-circle"></i> Drag a row to reorder it — this is the exact order customers see in the app.
        </p>

        <div class="cat-preview-bar">
            <span class="cat-preview-label"><i class="fas fa-mobile-alt"></i> App order:</span>
            <span class="cat-preview-list" id="catPreviewList">
                @php $activeCategories = $categories->reject->trashed()->values(); @endphp
                @forelse($activeCategories as $c)
                    <span class="cat-preview-chip">{{ $c->name }}</span>
                    @unless($loop->last)<i class="fas fa-arrow-right"></i>@endunless
                @empty
                    <span style="color:#6b7280;font-weight:400;">No categories yet</span>
                @endforelse
            </span>
        </div>

        <div class="table-responsive">
            <table class="table idx-table">
                <thead>
                    <tr>
                        <th style="width:60px;">Order</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Name (Arabic)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesSortable" data-reorder-url="{{ route('admin.categories.reorder') }}">
                    @php $position = 0; @endphp
                    @forelse($categories as $category)
                    @php if (!$category->trashed()) { $position++; } @endphp
                    <tr {!! !$category->trashed() ? 'class="cat-draggable-row" draggable="true"' : '' !!}
                        data-id="{{ $category->id }}" data-name="{{ $category->name }}">
                        <td>
                            @if(!$category->trashed())
                                <span class="cat-order-num">{{ $position }}</span>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td style="font-weight:600;color:#111827;">#{{ $category->id }}</td>
                        <td>
                            <span style="font-weight:600;">{{ $category->name }}</span>
                            @if($category->trashed())
                                <span class="idx-chip chip-red ml-1">Deleted</span>
                            @endif
                        </td>
                        <td dir="rtl" style="font-weight:600;">{{ $category->name_ar ?? '—' }}</td>
                        <td style="white-space:nowrap;">
                            @if(!$category->trashed())
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="idx-btn ib-edit">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                                </form>
                            @else
                                <form action="{{ route('admin.categories.restore', $category->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="idx-btn ib-green" onclick="return confirm('Restore this category?')">
                                        <i class="fas fa-undo"></i> Restore
                                    </button>
                                </form>
                                <form action="{{ route('admin.categories.force-delete', $category->id) }}" method="POST" style="display:inline-block;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="idx-btn ib-del"
                                            onclick="return confirm('Permanently delete? This removes the category from all meals.')">
                                        <i class="fas fa-times"></i> Force Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="idx-empty"><i class="fas fa-tags"></i><br>No categories found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
(function () {
    var tbody = document.getElementById('categoriesSortable');
    if (!tbody) return;

    var reorderUrl = tbody.dataset.reorderUrl;
    var csrfToken  = '{{ csrf_token() }}';
    var dragEl     = null;

    function rows() {
        return Array.prototype.slice.call(tbody.querySelectorAll('tr.cat-draggable-row'));
    }

    function renumberAndPreview() {
        var preview = document.getElementById('catPreviewList');
        if (preview) preview.innerHTML = '';

        rows().forEach(function (row, i) {
            var badge = row.querySelector('.cat-order-num');
            if (badge) badge.textContent = i + 1;

            if (preview) {
                var chip = document.createElement('span');
                chip.className = 'cat-preview-chip';
                chip.textContent = row.dataset.name || '';
                preview.appendChild(chip);

                if (i < rows().length - 1) {
                    var arrow = document.createElement('i');
                    arrow.className = 'fas fa-arrow-right';
                    preview.appendChild(arrow);
                }
            }
        });
    }

    function saveOrder() {
        var ids = rows().map(function (r) { return r.dataset.id; });

        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ order: ids }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Order saved', showConfirmButton: false, timer: 1400 });
            } else {
                throw new Error('save failed');
            }
        })
        .catch(function () {
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Could not save order — reloading', showConfirmButton: false, timer: 1800 });
            setTimeout(function () { location.reload(); }, 1200);
        });
    }

    rows().forEach(function (row) {
        row.addEventListener('dragstart', function () {
            dragEl = row;
            row.classList.add('cat-dragging');
        });
        row.addEventListener('dragend', function () {
            row.classList.remove('cat-dragging');
            dragEl = null;
        });
        row.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (!dragEl || dragEl === row) return;
            var rect  = row.getBoundingClientRect();
            var after = (e.clientY - rect.top) > (rect.height / 2);
            row.parentNode.insertBefore(dragEl, after ? row.nextSibling : row);
            renumberAndPreview();
        });
    });

    tbody.addEventListener('drop', function (e) {
        e.preventDefault();
        if (dragEl) saveOrder();
    });
})();
</script>
@endsection
