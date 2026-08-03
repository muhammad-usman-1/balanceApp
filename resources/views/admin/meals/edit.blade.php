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
.mf-err { font-size: .75rem; color: #ef4444; margin-top: 4px; }
.mf-section { font-size: .7rem; font-weight: 800; color: #9ca3af; text-transform: uppercase;
              letter-spacing: .08em; margin: 0 0 14px; padding-bottom: 8px;
              border-bottom: 1px solid #f3f4f6; }
.mf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.mf-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
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

/* Type pills */
.type-pill-wrap { display: flex; gap: 8px; }
.type-pill input[type=radio] { display: none; }
.type-pill label {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 8px; font-size: .82rem; font-weight: 600;
    border: 2px solid #e5e7eb; color: #6b7280; cursor: pointer; transition: all .15s;
}
.type-pill input[type=radio]:checked + label {
    border-color: #6366f1; background: #eef2ff; color: #4338ca;
}

/* Image upload zone */
.img-zone {
    border: 2px dashed #e5e7eb; border-radius: 12px;
    padding: 20px; text-align: center; cursor: pointer;
    transition: border-color .15s, background .15s;
    position: relative; background: #fafafa;
}
.img-zone:hover { border-color: #a5b4fc; background: #f5f3ff; }
.img-zone input[type=file] {
    position: absolute; inset: 0; width: 100%; height: 100%;
    opacity: 0; cursor: pointer;
}
.img-current {
    width: 100%; aspect-ratio: 1/1; max-height: 200px;
    object-fit: cover; border-radius: 10px;
    border: 1px solid #e5e7eb; margin-bottom: 10px; display: block;
}
.img-preview {
    width: 100%; aspect-ratio: 1/1; max-height: 200px;
    object-fit: cover; border-radius: 10px;
    border: 1px solid #a5b4fc; margin-bottom: 10px; display: none;
}
</style>

@php $currentImage = $meal->getFirstMedia('image'); @endphp

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-pen mr-2" style="color:#b45309;"></i> Edit Meal</h3>
        <a href="{{ route('admin.meals.index') }}" class="idx-btn ib-gray">
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
        <form method="POST" action="{{ route('admin.meals.update', [$meal->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 280px; gap: 28px; align-items: start;">

                {{-- ── LEFT: Form fields ── --}}
                <div>

                    {{-- Basic Info --}}
                    <p class="mf-section"><i class="fas fa-info-circle mr-1"></i> Basic Info</p>

                    <div class="mf-grid-2" style="margin-bottom:0;">
                        <div class="mf-field">
                            <label class="mf-label" for="title">Meal Name</label>
                            <input class="mf-input {{ $errors->has('title') ? 'is-err' : '' }}"
                                   type="text" name="title" id="title"
                                   value="{{ old('title', $meal->title) }}" placeholder="e.g. Grilled Chicken Breast">
                            @if($errors->has('title'))
                                <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('title') }}</div>
                            @endif
                        </div>
                        <div class="mf-field">
                            <label class="mf-label" for="title_ar">Meal Name (Arabic)</label>
                            <input class="mf-input {{ $errors->has('title_ar') ? 'is-err' : '' }}"
                                   type="text" name="title_ar" id="title_ar" dir="rtl"
                                   value="{{ old('title_ar', $meal->title_ar) }}" placeholder="مثال: صدر دجاج مشوي">
                            @if($errors->has('title_ar'))
                                <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('title_ar') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="mf-field">
                        <label class="mf-label" for="description">Description</label>
                        <textarea class="mf-input {{ $errors->has('description') ? 'is-err' : '' }}"
                                  name="description" id="description"
                                  rows="3" placeholder="Short description of the meal…"
                                  style="resize:vertical;">{{ old('description', $meal->description) }}</textarea>
                        @if($errors->has('description'))
                            <div class="mf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('description') }}</div>
                        @endif
                    </div>

                    <div class="mf-grid-2" style="margin-bottom:16px;">
                        <div>
                            <label class="mf-label" for="category_id">Category</label>
                            <select class="mf-input {{ $errors->has('category_id') ? 'is-err' : '' }}"
                                    name="category_id" id="category_id">
                                <option value="">— Select category —</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $meal->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($errors->has('category_id'))
                                <div class="mf-err">{{ $errors->first('category_id') }}</div>
                            @endif
                        </div>
                        <div>
                            <label class="mf-label" for="extras">Extras / Allergens</label>
                            <input class="mf-input {{ $errors->has('extras') ? 'is-err' : '' }}"
                                   type="text" name="extras" id="extras"
                                   value="{{ old('extras', $meal->extras) }}" placeholder="e.g. nuts, dairy">
                            @if($errors->has('extras'))
                                <div class="mf-err">{{ $errors->first('extras') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Nutrition --}}
                    <p class="mf-section" style="margin-top:10px;"><i class="fas fa-fire-alt mr-1"></i> Nutrition Facts</p>

                    <div class="mf-grid-4" style="margin-bottom:16px;">
                        <div>
                            <label class="mf-label" for="calories">
                                <i class="fas fa-fire" style="color:#f97316;"></i> Calories
                            </label>
                            <input class="mf-input {{ $errors->has('calories') ? 'is-err' : '' }}"
                                   type="number" name="calories" id="calories"
                                   value="{{ old('calories', $meal->calories) }}" step="1" placeholder="kcal">
                            @if($errors->has('calories'))
                                <div class="mf-err">{{ $errors->first('calories') }}</div>
                            @endif
                        </div>
                        <div>
                            <label class="mf-label" for="protein_g">
                                <i class="fas fa-dumbbell" style="color:#6366f1;"></i> Protein (g)
                            </label>
                            <input class="mf-input {{ $errors->has('protein_g') ? 'is-err' : '' }}"
                                   type="number" name="protein_g" id="protein_g"
                                   value="{{ old('protein_g', $meal->protein_g) }}" step="0.1" placeholder="0.0">
                            @if($errors->has('protein_g'))
                                <div class="mf-err">{{ $errors->first('protein_g') }}</div>
                            @endif
                        </div>
                        <div>
                            <label class="mf-label" for="carbs_g">
                                <i class="fas fa-bread-slice" style="color:#f59e0b;"></i> Carbs (g)
                            </label>
                            <input class="mf-input {{ $errors->has('carbs_g') ? 'is-err' : '' }}"
                                   type="number" name="carbs_g" id="carbs_g"
                                   value="{{ old('carbs_g', $meal->carbs_g) }}" step="0.1" placeholder="0.0">
                            @if($errors->has('carbs_g'))
                                <div class="mf-err">{{ $errors->first('carbs_g') }}</div>
                            @endif
                        </div>
                        <div>
                            <label class="mf-label" for="fat_g">
                                <i class="fas fa-tint" style="color:#22c55e;"></i> Fat (g)
                            </label>
                            <input class="mf-input {{ $errors->has('fat_g') ? 'is-err' : '' }}"
                                   type="number" name="fat_g" id="fat_g"
                                   value="{{ old('fat_g', $meal->fat_g) }}" step="0.1" placeholder="0.0">
                            @if($errors->has('fat_g'))
                                <div class="mf-err">{{ $errors->first('fat_g') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Settings --}}
                    <p class="mf-section" style="margin-top:10px;"><i class="fas fa-sliders-h mr-1"></i> Settings</p>

                    <div class="mf-grid-2">
                        <div>
                            <label class="mf-label">Type</label>
                            <div class="type-pill-wrap">
                                @foreach(App\Models\Meal::TYPE_RADIO as $key => $label)
                                <div class="type-pill">
                                    <input type="radio" id="type_{{ $key }}" name="type"
                                           value="{{ $key }}" {{ old('type', $meal->type) === $key ? 'checked' : '' }}>
                                    <label for="type_{{ $key }}">
                                        @if($key === 'is meal')
                                            <i class="fas fa-utensils"></i> Meal
                                        @else
                                            <i class="fas fa-cookie-bite"></i> Snack
                                        @endif
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @if($errors->has('type'))
                                <div class="mf-err">{{ $errors->first('type') }}</div>
                            @endif
                        </div>
                        <div>
                            <label class="mf-label">Status</label>
                            <input type="hidden" name="is_active" value="0">
                            <div class="toggle-wrap" style="margin-top:4px;">
                                <label class="toggle">
                                    <input type="checkbox" name="is_active" id="is_active"
                                           value="1" {{ old('is_active', $meal->is_active) == 1 ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label" id="toggle-text">
                                    {{ old('is_active', $meal->is_active) == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── RIGHT: Image ── --}}
                <div>
                    <p class="mf-section"><i class="fas fa-image mr-1"></i> Meal Image</p>

                    {{-- Show current image --}}
                    @if($currentImage && $currentImage->getUrl())
                        <img id="img-preview" class="img-preview"
                             src="{{ $currentImage->getUrl() }}"
                             alt="Current image"
                             style="display:block;">
                    @else
                        <img id="img-preview" class="img-preview" src="" alt="Preview">
                    @endif

                    <div class="img-zone" id="img-zone">
                        <input type="file" name="image" id="image" accept="image/*">
                        <div id="img-zone-content">
                            <i class="fas fa-camera" style="font-size:1.4rem; color:#a5b4fc; margin-bottom:8px; display:block;"></i>
                            <div style="font-size:.78rem; font-weight:600; color:#374151;">
                                {{ $currentImage ? 'Click to replace image' : 'Click to upload image' }}
                            </div>
                            <div style="font-size:.7rem; color:#9ca3af; margin-top:3px;">PNG, JPG up to 5MB</div>
                        </div>
                    </div>
                    @if($errors->has('image'))
                        <div class="mf-err mt-1">{{ $errors->first('image') }}</div>
                    @endif
                </div>

            </div>

            {{-- Actions --}}
            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #f3f4f6;
                        display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="idx-btn ib-edit" style="padding: 9px 22px; font-size: .85rem;">
                    <i class="fas fa-check"></i> Save Changes
                </button>
                <a href="{{ route('admin.meals.index') }}" class="idx-btn ib-gray" style="padding: 9px 18px; font-size: .85rem;">
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
    const input     = document.getElementById('image');
    const preview   = document.getElementById('img-preview');
    const toggle    = document.getElementById('is_active');
    const toggleTxt = document.getElementById('toggle-text');

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    toggle.addEventListener('change', function() {
        toggleTxt.textContent = this.checked ? 'Active' : 'Inactive';
    });
})();
</script>
@endsection
