@extends('layouts.admin')

@section('content')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add Coupon</h3>
            </div>
            <div class="card-body">
        <form action="{{ route('admin.coupons.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="coupon_code">Coupon Code <span class="text-danger">*</span></label>
                <input type="text" name="coupon_code" id="coupon_code" class="form-control @error('coupon_code') is-invalid @enderror" value="{{ old('coupon_code') }}" required>
                @error('coupon_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="type">Type <span class="text-danger">*</span></label>
                <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                    <option value="">Select Type</option>
                    <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                    <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="value">Value <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="value" id="value" step="0.01" min="0" class="form-control @error('value') is-invalid @enderror" value="{{ old('value') }}" required>
                    <div class="input-group-append">
                        <span class="input-group-text" id="value_suffix">-</span>
                    </div>
                </div>
                <small class="form-text text-muted" id="value_help">Enter the discount value</small>
                @error('value')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="end_date">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
                @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="status">Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="usage_limit_per_user">Usage Limit Per User</label>
                <input type="number" name="usage_limit_per_user" id="usage_limit_per_user"
                       class="form-control @error('usage_limit_per_user') is-invalid @enderror"
                       value="{{ old('usage_limit_per_user') }}" min="1" placeholder="Leave empty for unlimited">
                <small class="form-text text-muted">
                    Maximum number of times a single user can use this coupon. Leave empty for unlimited usage.
                </small>
                @error('usage_limit_per_user')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
                <button type="submit" class="btn btn-success">Submit</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Back</a>
            </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const valueSuffix = document.getElementById('value_suffix');
    const valueHelp = document.getElementById('value_help');
    const valueInput = document.getElementById('value');

    function updateValueDisplay() {
        const type = typeSelect.value;
        if (type === 'percentage') {
            valueSuffix.textContent = '%';
            valueHelp.textContent = 'Enter percentage value (0-100)';
            valueInput.setAttribute('max', '100');
        } else if (type === 'fixed') {
            valueSuffix.textContent = '';
            valueHelp.textContent = 'Enter the fixed discount amount';
            valueInput.removeAttribute('max');
        } else {
            valueSuffix.textContent = '-';
            valueHelp.textContent = 'Enter the discount value';
        }
    }

    typeSelect.addEventListener('change', updateValueDisplay);
    updateValueDisplay();
});
</script>
@endsection

