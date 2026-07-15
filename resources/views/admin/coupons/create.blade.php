@extends('layouts.admin')

@section('content')

@include('partials.idx-styles')
<style>
.content-wrapper { background: #fff !important; }

.cp-wrap { max-width: 720px; }

.cp-group { margin-bottom: 18px; }
.cp-group label {
    font-size: .78rem; font-weight: 600; color: #374151;
    display: block; margin-bottom: 6px;
}
.cp-group label .req { color: #ef4444; }
.cp-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media(max-width:576px){ .cp-row { grid-template-columns: 1fr; } }

.cp-input, .cp-select {
    width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 9px;
    font-size: .87rem; color: #111827; background: #fff;
    transition: border-color .15s, box-shadow .15s; outline: none;
}
.cp-input:focus, .cp-select:focus { border-color: #db2777; box-shadow: 0 0 0 3px rgba(219,39,119,.12); }
.cp-input.is-invalid, .cp-select.is-invalid { border-color: #dc2626; }
.cp-help { font-size: .75rem; color: #9ca3af; margin-top: 5px; }
.cp-error { font-size: .75rem; color: #dc2626; margin-top: 5px; }

.cp-value-group { display: flex; }
.cp-value-group .cp-input { border-radius: 9px 0 0 9px; }
.cp-value-suffix {
    display: flex; align-items: center; padding: 0 14px; white-space: nowrap;
    border: 1px solid #d1d5db; border-left: none; border-radius: 0 9px 9px 0;
    background: #f9fafb; color: #6b7280; font-size: .85rem; font-weight: 600;
}

.cp-type-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.cp-type-opt {
    display: flex; align-items: center; gap: 8px; padding: 10px 14px;
    border: 1px solid #e5e7eb; border-radius: 9px; cursor: pointer;
    font-size: .84rem; font-weight: 600; color: #374151;
    transition: border-color .15s, background .15s;
}
.cp-type-opt:has(input:checked) { border-color: #db2777; background: #fdf2f8; color: #db2777; }
.cp-type-opt input { position: absolute; opacity: 0; width: 0; height: 0; }

.cp-status-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.cp-status-opt {
    display: flex; align-items: center; gap: 8px; padding: 10px 14px;
    border: 1px solid #e5e7eb; border-radius: 9px; cursor: pointer;
    font-size: .84rem; font-weight: 600; color: #374151;
    transition: border-color .15s, background .15s;
}
.cp-status-opt:has(input:checked) { border-color: #15803d; background: #f0fdf4; color: #15803d; }
.cp-status-opt input { position: absolute; opacity: 0; width: 0; height: 0; }

.cp-actions { display: flex; gap: 10px; padding-top: 6px; }
.cp-btn-submit {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; border-radius: 9px; border: none; cursor: pointer;
    font-size: .86rem; font-weight: 600; color: #fff;
    background: linear-gradient(135deg,#db2777,#be185d); transition: opacity .15s;
}
.cp-btn-submit:hover { opacity: .88; }
.cp-btn-cancel {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 16px; border-radius: 9px; border: 1px solid #e5e7eb; cursor: pointer;
    font-size: .86rem; font-weight: 600; color: #6b7280; background: #fff;
    text-decoration: none; transition: background .15s;
}
.cp-btn-cancel:hover { background: #f9fafb; color: #6b7280; text-decoration: none; }
</style>

<div class="cp-wrap">
    <div class="card idx-card">
        <div class="card-header">
            <h3><i class="fas fa-ticket-alt mr-2" style="color:#db2777;"></i> Add Coupon</h3>
        </div>

        <div class="card-body" style="padding:22px !important;">
            <form action="{{ route('admin.coupons.store') }}" method="POST">
                @csrf

                <div class="cp-row">
                    <div class="cp-group">
                        <label for="coupon_code">Coupon Code <span class="req">*</span></label>
                        <input type="text" name="coupon_code" id="coupon_code"
                               class="cp-input @error('coupon_code') is-invalid @enderror"
                               placeholder="e.g. SUMMER20" value="{{ old('coupon_code') }}" required>
                        @error('coupon_code')<div class="cp-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cp-group">
                        <label for="usage_limit_per_user">Usage Limit Per User</label>
                        <input type="number" name="usage_limit_per_user" id="usage_limit_per_user"
                               class="cp-input @error('usage_limit_per_user') is-invalid @enderror"
                               value="{{ old('usage_limit_per_user') }}" min="1" placeholder="Unlimited">
                        @error('usage_limit_per_user')
                            <div class="cp-error">{{ $message }}</div>
                        @else
                            <div class="cp-help">Leave empty for unlimited usage</div>
                        @enderror
                    </div>
                </div>

                <div class="cp-group">
                    <label>Discount Type <span class="req">*</span></label>
                    <div class="cp-type-row">
                        <label class="cp-type-opt">
                            <input type="radio" name="type" value="fixed" {{ old('type') === 'fixed' ? 'checked' : '' }} required>
                            Fixed Amount
                        </label>
                        <label class="cp-type-opt">
                            <input type="radio" name="type" value="percentage" {{ old('type') === 'percentage' ? 'checked' : '' }}>
                            Percentage
                        </label>
                    </div>
                    @error('type')<div class="cp-error">{{ $message }}</div>@enderror
                </div>

                <div class="cp-group">
                    <label for="value">Value <span class="req">*</span></label>
                    <div class="cp-value-group">
                        <input type="number" name="value" id="value" step="0.01" min="0"
                               class="cp-input @error('value') is-invalid @enderror"
                               value="{{ old('value') }}" required>
                        <span class="cp-value-suffix" id="value_suffix">—</span>
                    </div>
                    @error('value')
                        <div class="cp-error">{{ $message }}</div>
                    @else
                        <div class="cp-help" id="value_help">Select a discount type first</div>
                    @enderror
                </div>

                <div class="cp-row">
                    <div class="cp-group">
                        <label for="start_date">Start Date <span class="req">*</span></label>
                        <input type="date" name="start_date" id="start_date"
                               class="cp-input @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}" required>
                        @error('start_date')<div class="cp-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cp-group">
                        <label for="end_date">End Date <span class="req">*</span></label>
                        <input type="date" name="end_date" id="end_date"
                               class="cp-input @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}" required>
                        @error('end_date')<div class="cp-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="cp-group">
                    <label>Status <span class="req">*</span></label>
                    <div class="cp-status-row">
                        <label class="cp-status-opt">
                            <input type="radio" name="status" value="active" {{ old('status', 'active') === 'active' ? 'checked' : '' }} required>
                            Active
                        </label>
                        <label class="cp-status-opt">
                            <input type="radio" name="status" value="inactive" {{ old('status') === 'inactive' ? 'checked' : '' }}>
                            Inactive
                        </label>
                    </div>
                    @error('status')<div class="cp-error">{{ $message }}</div>@enderror
                </div>

                <div class="cp-actions">
                    <button type="submit" class="cp-btn-submit"><i class="fas fa-plus"></i> Create Coupon</button>
                    <a href="{{ route('admin.coupons.index') }}" class="cp-btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeInputs = document.querySelectorAll('input[name="type"]');
    const valueSuffix = document.getElementById('value_suffix');
    const valueHelp = document.getElementById('value_help');
    const valueInput = document.getElementById('value');

    function updateValueDisplay() {
        const checked = document.querySelector('input[name="type"]:checked');
        const type = checked ? checked.value : '';
        if (type === 'percentage') {
            valueSuffix.textContent = '%';
            valueHelp.textContent = 'Enter percentage value (0-100)';
            valueInput.setAttribute('max', '100');
        } else if (type === 'fixed') {
            valueSuffix.textContent = 'KWD';
            valueHelp.textContent = 'Enter the fixed discount amount';
            valueInput.removeAttribute('max');
        } else {
            valueSuffix.textContent = '—';
            valueHelp.textContent = 'Select a discount type first';
        }
    }

    typeInputs.forEach(input => input.addEventListener('change', updateValueDisplay));
    updateValueDisplay();
});
</script>
@endsection
