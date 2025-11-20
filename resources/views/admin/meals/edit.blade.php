@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.meal.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.meals.update", [$meal->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="title">{{ trans('cruds.meal.fields.title') }}</label>
                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', $meal->title) }}">
                @if($errors->has('title'))
                    <span class="text-danger">{{ $errors->first('title') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.title_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="description">{{ trans('cruds.meal.fields.description') }}</label>
                <input class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" type="text" name="description" id="description" value="{{ old('description', $meal->description) }}">
                @if($errors->has('description'))
                    <span class="text-danger">{{ $errors->first('description') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.description_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="image">{{ trans('cruds.meal.fields.image') }}</label>
                <input class="form-control {{ $errors->has('image') ? 'is-invalid' : '' }}" type="file" name="image" id="image" accept="image/*">
                @if($errors->has('image'))
                    <span class="text-danger">{{ $errors->first('image') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.image_helper') }}</span>
                @php
                    $currentImage = $meal->getFirstMedia('image');
                @endphp
                @if($currentImage && $currentImage->getUrl())
                    <div style="margin-top: 10px;">
                        <p>Current Image:</p>
                        <img src="{{ $currentImage->getUrl() }}" alt="Current Image" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                @endif
                <div id="image-preview" style="margin-top: 10px;">
                    <p>New Image Preview:</p>
                    <img id="image-preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; display: none; border: 1px solid #ddd; padding: 5px;">
                </div>
            </div>
            <div class="form-group">
                <label for="category_id">{{ trans('cruds.meal.fields.category') }}</label>
                <select class="form-control {{ $errors->has('category_id') ? 'is-invalid' : '' }}" name="category_id" id="category_id">
                    <option value="">Select Category</option>
                    @if(isset($categories))
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $meal->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    @else
                        @foreach(App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $meal->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    @endif
                </select>
                @if($errors->has('category_id'))
                    <span class="text-danger">{{ $errors->first('category_id') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.category_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="calories">{{ trans('cruds.meal.fields.calories') }}</label>
                <input class="form-control {{ $errors->has('calories') ? 'is-invalid' : '' }}" type="number" name="calories" id="calories" value="{{ old('calories', $meal->calories) }}" step="1">
                @if($errors->has('calories'))
                    <span class="text-danger">{{ $errors->first('calories') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.calories_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="protein_g">{{ trans('cruds.meal.fields.protein_g') }}</label>
                <input class="form-control {{ $errors->has('protein_g') ? 'is-invalid' : '' }}" type="number" name="protein_g" id="protein_g" value="{{ old('protein_g', $meal->protein_g) }}" step="0.01">
                @if($errors->has('protein_g'))
                    <span class="text-danger">{{ $errors->first('protein_g') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.protein_g_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="fat_g">{{ trans('cruds.meal.fields.fat_g') }}</label>
                <input class="form-control {{ $errors->has('fat_g') ? 'is-invalid' : '' }}" type="number" name="fat_g" id="fat_g" value="{{ old('fat_g', $meal->fat_g) }}" step="0.01">
                @if($errors->has('fat_g'))
                    <span class="text-danger">{{ $errors->first('fat_g') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.fat_g_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="carbs_g">{{ trans('cruds.meal.fields.carbs_g') }}</label>
                <input class="form-control {{ $errors->has('carbs_g') ? 'is-invalid' : '' }}" type="number" name="carbs_g" id="carbs_g" value="{{ old('carbs_g', $meal->carbs_g) }}" step="0.01">
                @if($errors->has('carbs_g'))
                    <span class="text-danger">{{ $errors->first('carbs_g') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.carbs_g_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="extras">{{ trans('cruds.meal.fields.extras') }}</label>
                <input class="form-control {{ $errors->has('extras') ? 'is-invalid' : '' }}" type="text" name="extras" id="extras" value="{{ old('extras', $meal->extras) }}">
                @if($errors->has('extras'))
                    <span class="text-danger">{{ $errors->first('extras') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.extras_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="is_active">{{ trans('cruds.meal.fields.is_active') }}</label>
                <div class="form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $meal->is_active) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                @if($errors->has('is_active'))
                    <span class="text-danger">{{ $errors->first('is_active') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.is_active_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.meal.fields.type') }}</label>
                @foreach(App\Models\Meal::TYPE_RADIO as $key => $label)
                    <div class="form-check {{ $errors->has('type') ? 'is-invalid' : '' }}">
                        <input class="form-check-input" type="radio" id="type_{{ $key }}" name="type" value="{{ $key }}" {{ old('type', $meal->type) === (string) $key ? 'checked' : '' }}>
                        <label class="form-check-label" for="type_{{ $key }}">{{ $label }}</label>
                    </div>
                @endforeach
                @if($errors->has('type'))
                    <span class="text-danger">{{ $errors->first('type') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.meal.fields.type_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection

@section('scripts')
<script>
    // Image preview functionality
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('image-preview-img');
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('image-preview-img').style.display = 'none';
        }
    });
</script>
@endsection
