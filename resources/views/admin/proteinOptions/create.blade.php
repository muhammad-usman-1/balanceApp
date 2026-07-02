@extends('layouts.admin')

@section('content')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="row">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add Protein Option</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.protein-options.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="protein_grams">Protein Amount (grams) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                   name="protein_grams"
                                   id="protein_grams"
                                   class="form-control @error('protein_grams') is-invalid @enderror"
                                   value="{{ old('protein_grams') }}"
                                   min="1"
                                   placeholder="e.g. 150"
                                   required>
                            <div class="input-group-append">
                                <span class="input-group-text">g</span>
                            </div>
                        </div>
                        @error('protein_grams')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="extra_price_per_meal">Extra Price Per Meal (KWD) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                   name="extra_price_per_meal"
                                   id="extra_price_per_meal"
                                   class="form-control @error('extra_price_per_meal') is-invalid @enderror"
                                   value="{{ old('extra_price_per_meal') }}"
                                   step="0.001"
                                   min="0"
                                   placeholder="e.g. 0.650"
                                   required>
                            <div class="input-group-append">
                                <span class="input-group-text">KWD</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            This amount is multiplied by (meals per day × delivery days) to get the total surcharge.
                        </small>
                        @error('extra_price_per_meal')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active (visible to app)</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Save</button>
                    <a href="{{ route('admin.protein-options.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
