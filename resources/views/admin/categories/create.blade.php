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
.mf-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.1); }
.mf-input.is-err { border-color: #ef4444; }
.mf-err { font-size: .75rem; color: #ef4444; margin-top: 4px; }
.mf-section { font-size: .7rem; font-weight: 800; color: #9ca3af; text-transform: uppercase;
              letter-spacing: .08em; margin: 0 0 14px; padding-bottom: 8px;
              border-bottom: 1px solid #f3f4f6; }
.mf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.mf-field { margin-bottom: 16px; }
@media(max-width:640px){ .mf-grid-2 { grid-template-columns: 1fr; } }
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle mr-2" style="color:#22c55e;"></i> Add Category</h3>
        <a href="{{ route('admin.categories.index') }}" class="idx-btn ib-gray">
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
        <form action="{{ route('admin.categories.store') }}" method="POST" style="max-width:640px;">
            @csrf

            <p class="mf-section"><i class="fas fa-tags mr-1"></i> Category Details</p>

            <div class="mf-grid-2">
                <div class="mf-field">
                    <label class="mf-label" for="name">Category Name</label>
                    <input class="mf-input {{ $errors->has('name') ? 'is-err' : '' }}"
                           type="text" name="name" id="name"
                           value="{{ old('name') }}" placeholder="e.g. Breakfast" required>
                    @if($errors->has('name'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('name') }}</div>
                    @endif
                </div>
                <div class="mf-field">
                    <label class="mf-label" for="name_ar">Category Name (Arabic)</label>
                    <input class="mf-input {{ $errors->has('name_ar') ? 'is-err' : '' }}"
                           type="text" name="name_ar" id="name_ar" dir="rtl"
                           value="{{ old('name_ar') }}" placeholder="مثال: فطور">
                    @if($errors->has('name_ar'))
                        <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('name_ar') }}</div>
                    @endif
                </div>
            </div>

            <div style="margin-top: 12px; padding-top: 20px; border-top: 1px solid #f3f4f6;
                        display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="idx-btn ib-green" style="padding: 9px 22px; font-size: .85rem;">
                    <i class="fas fa-check"></i> Save Category
                </button>
                <a href="{{ route('admin.categories.index') }}" class="idx-btn ib-gray" style="padding: 9px 18px; font-size: .85rem;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
