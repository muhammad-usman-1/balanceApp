@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
@include('admin.mealExtras._styles')
<style>
    .ing-item { display:flex; align-items:center; gap:8px; padding:10px 12px; border:1px solid #f3f4f6;
                border-radius:10px; background:#fafafa; margin-bottom:8px; }
    .ing-edit-form { display:grid; grid-template-columns: 1fr 1fr 210px auto; gap:10px; align-items:center; flex:1; }
    .ing-input { width:100%; padding:7px 10px; font-size:.83rem; border:1px solid #e5e7eb; border-radius:7px;
                 outline:none; background:#fff; box-sizing:border-box; }
    .ing-input:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(13,148,136,.1); }
    .ing-add { display:grid; grid-template-columns: 1fr 1fr 120px auto; gap:10px; align-items:end;
               padding:14px; border:1px dashed #cbd5e1; border-radius:10px; background:#f8fafc; margin-bottom:16px; }
    /* Column header above the existing options — mirrors the row grid so labels line up */
    .ing-head { display:flex; align-items:center; gap:8px; padding:0 12px 6px; }
    .ing-head-grid { display:grid; grid-template-columns: 1fr 1fr 210px auto; gap:10px; flex:1; }
    .ing-head span { font-size:.68rem; font-weight:800; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; }
    .ing-head-del { width:36px; text-align:center; flex-shrink:0; }
    /* Tiny inline labels inside each row (so fields are clear even on mobile) */
    .ing-mini { font-size:.66rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.03em; }
    @media(max-width:720px){ .ing-edit-form, .ing-add { grid-template-columns: 1fr; } .ing-head { display:none; } }
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-pen mr-2" style="color:#b45309;"></i> Manage Extra  {{ $mealExtra->name }}</h3>
        <a href="{{ route('admin.meal-extras.index') }}" class="idx-btn ib-gray">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="idx-flash idx-flash-error" style="margin:16px 20px 0;">
        <i class="fas fa-exclamation-circle"></i> Please fix the errors below before saving.
    </div>
    @endif

    <div class="card-body" style="padding: 24px !important;">

        {{-- ── Extra details ── --}}
        <form action="{{ route('admin.meal-extras.update', $mealExtra->id) }}" method="POST" style="max-width:680px;">
            @csrf @method('PUT')
            <p class="mf-section"><i class="fas fa-plus-square mr-1"></i> Extra Details</p>

            @include('admin.mealExtras._fields', ['mealExtra' => $mealExtra])

            <div style="margin-top: 12px; display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="idx-btn ib-edit" style="padding: 9px 22px; font-size: .85rem;">
                    <i class="fas fa-check"></i> Update Extra
                </button>
            </div>
        </form>

        {{-- ── Options / ingredients ── --}}
        <p class="mf-section" style="margin-top:28px;"><i class="fas fa-list-ul mr-1"></i> Options ({{ $mealExtra->ingredients->count() }})</p>

        {{-- Add option --}}
        <form action="{{ route('admin.meal-extra-ingredients.store') }}" method="POST" class="ing-add">
            @csrf
            <input type="hidden" name="meal_extra_id" value="{{ $mealExtra->id }}">
            <div>
                <label class="mf-label" for="new_name">Option Name</label>
                <input class="ing-input" type="text" name="name" id="new_name" placeholder="e.g. Brown Bread" required>
            </div>
            <div>
                <label class="mf-label" for="new_name_ar">Name (Arabic)</label>
                <input class="ing-input" type="text" name="name_ar" id="new_name_ar" dir="rtl" placeholder="مثال: خبز بني">
            </div>
            <div>
                <label class="mf-label" for="new_sort">Sort</label>
                <input class="ing-input" type="number" name="sort_order" id="new_sort" min="0" value="0">
            </div>
            <div>
                <button type="submit" class="idx-btn ib-green" style="width:100%;justify-content:center;"><i class="fas fa-plus"></i> Add</button>
            </div>
        </form>

        {{-- Existing options --}}
        @if($mealExtra->ingredients->count())
        <div class="ing-head">
            <div class="ing-head-grid">
                <span>Option Name</span>
                <span>Name (Arabic)</span>
                <span>Active &amp; Sort</span>
                <span>Save</span>
            </div>
            <span class="ing-head-del">Delete</span>
        </div>
        @endif
        @forelse($mealExtra->ingredients as $ing)
            <div class="ing-item">
                <form action="{{ route('admin.meal-extra-ingredients.update', $ing->id) }}" method="POST" class="ing-edit-form">
                    @csrf @method('PUT')
                    <input class="ing-input" type="text" name="name" value="{{ $ing->name }}" required
                           title="Option name (English)" aria-label="Option name">
                    <input class="ing-input" type="text" name="name_ar" value="{{ $ing->name_ar }}" dir="rtl" placeholder="—"
                           title="Option name (Arabic)" aria-label="Option name Arabic">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="hidden" name="is_active" value="0">
                        <span class="ing-mini">Active</span>
                        <label class="toggle" style="width:38px;height:22px;" title="Active / inactive">
                            <input type="checkbox" name="is_active" value="1" {{ $ing->is_active ? 'checked' : '' }}
                                   aria-label="Active">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="ing-mini">Sort</span>
                        <input class="ing-input" type="number" name="sort_order" value="{{ $ing->sort_order }}" min="0"
                               style="width:56px;" title="Sort order" aria-label="Sort order">
                    </div>
                    <button type="submit" class="idx-btn ib-edit" title="Save changes"><i class="fas fa-save"></i></button>
                </form>
                <form action="{{ route('admin.meal-extra-ingredients.destroy', $ing->id) }}" method="POST"
                      onsubmit="return confirm('Delete this option?');" style="flex-shrink:0;">
                    @csrf @method('DELETE')
                    <button type="submit" class="idx-btn ib-del" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        @empty
            <div class="idx-empty" style="padding:24px;"><i class="fas fa-list-ul"></i><br>No options yet. Add the first one above.</div>
        @endforelse
    </div>
</div>

@endsection
