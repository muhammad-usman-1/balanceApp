@extends('layouts.admin')

@section('content')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="row">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Protein Option — {{ $proteinOption->protein_grams }}g</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.protein-options.update', $proteinOption->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="protein_grams">Protein Amount (grams) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                   name="protein_grams"
                                   id="protein_grams"
                                   class="form-control @error('protein_grams') is-invalid @enderror"
                                   value="{{ old('protein_grams', $proteinOption->protein_grams) }}"
                                   min="1"
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
                                   value="{{ old('extra_price_per_meal', $proteinOption->extra_price_per_meal) }}"
                                   step="0.001"
                                   min="0"
                                   required>
                            <div class="input-group-append">
                                <span class="input-group-text">KWD</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Changing this will apply to all new subscriptions from this point forward.
                            Existing subscriptions are not affected.
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
                                   {{ old('is_active', $proteinOption->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active (visible to app)</label>
                        </div>
                        <small class="form-text text-muted">Inactive options will not appear in the mobile app.</small>
                    </div>

                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="{{ route('admin.protein-options.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
