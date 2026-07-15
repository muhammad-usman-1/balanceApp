@extends('layouts.admin')

@section('content')

@include('partials.idx-styles')
<style>
.content-wrapper { background: #fff !important; }

.pp-wrap { max-width: 640px; }

.pp-group { margin-bottom: 18px; }
.pp-group label {
    font-size: .78rem; font-weight: 600; color: #374151;
    display: block; margin-bottom: 6px;
}
.pp-group label .req { color: #ef4444; }

.pp-input {
    width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 9px;
    font-size: .87rem; color: #111827; background: #fff;
    transition: border-color .15s, box-shadow .15s; outline: none;
}
.pp-input:focus { border-color: #d97706; box-shadow: 0 0 0 3px rgba(217,119,6,.12); }
.pp-input.is-invalid { border-color: #dc2626; }
.pp-help { font-size: .75rem; color: #9ca3af; margin-top: 5px; }
.pp-error { font-size: .75rem; color: #dc2626; margin-top: 5px; }

.pp-suffix-group { display: flex; }
.pp-suffix-group .pp-input { border-radius: 9px 0 0 9px; }
.pp-suffix {
    display: flex; align-items: center; padding: 0 14px; white-space: nowrap;
    border: 1px solid #d1d5db; border-left: none; border-radius: 0 9px 9px 0;
    background: #f9fafb; color: #6b7280; font-size: .85rem; font-weight: 600;
}

.pp-switch-row {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 9px;
}
.pp-switch-label { font-size: .84rem; font-weight: 600; color: #374151; }
.pp-switch-sub { font-size: .75rem; color: #9ca3af; margin-top: 2px; }
.pp-switch { position: relative; display: inline-block; width: 42px; height: 24px; flex-shrink: 0; }
.pp-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
.pp-switch-track {
    position: absolute; inset: 0; background: #d1d5db; border-radius: 999px;
    cursor: pointer; transition: background .15s;
}
.pp-switch-track::before {
    content: ""; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px;
    background: #fff; border-radius: 50%; transition: transform .15s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.pp-switch input:checked + .pp-switch-track { background: #d97706; }
.pp-switch input:checked + .pp-switch-track::before { transform: translateX(18px); }

.pp-info {
    display: flex; gap: 10px; padding: 10px 14px; background: #eff6ff; color: #1d4ed8;
    border: 1px solid #bfdbfe; border-radius: 8px; font-size: .8rem; margin-bottom: 18px;
}

.pp-actions { display: flex; gap: 10px; padding-top: 6px; }
.pp-btn-submit {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; border-radius: 9px; border: none; cursor: pointer;
    font-size: .86rem; font-weight: 600; color: #fff;
    background: linear-gradient(135deg,#d97706,#b45309); transition: opacity .15s;
}
.pp-btn-submit:hover { opacity: .88; }
.pp-btn-cancel {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 16px; border-radius: 9px; border: 1px solid #e5e7eb; cursor: pointer;
    font-size: .86rem; font-weight: 600; color: #6b7280; background: #fff;
    text-decoration: none; transition: background .15s;
}
.pp-btn-cancel:hover { background: #f9fafb; color: #6b7280; text-decoration: none; }
</style>

<div class="pp-wrap">
    <div class="card idx-card">
        <div class="card-header">
            <h3><i class="fas fa-dumbbell mr-2" style="color:#d97706;"></i> Add Protein Option</h3>
        </div>

        <div class="card-body" style="padding:22px !important;">
            <div class="pp-info">
                <i class="fas fa-info-circle" style="margin-top:2px;"></i>
                <div><strong>Pricing formula:</strong> Extra charge = Extra Price per Meal &times; Meals per Day &times; Delivery Days per Week.</div>
            </div>

            <form action="{{ route('admin.protein-options.store') }}" method="POST">
                @csrf

                <div class="pp-group">
                    <label for="protein_grams">Protein Amount <span class="req">*</span></label>
                    <div class="pp-suffix-group">
                        <input type="number" name="protein_grams" id="protein_grams"
                               class="pp-input @error('protein_grams') is-invalid @enderror"
                               value="{{ old('protein_grams') }}" min="1" placeholder="e.g. 150" required>
                        <span class="pp-suffix">g</span>
                    </div>
                    @error('protein_grams')<div class="pp-error">{{ $message }}</div>@enderror
                </div>

                <div class="pp-group">
                    <label for="extra_price_per_meal">Extra Price Per Meal <span class="req">*</span></label>
                    <div class="pp-suffix-group">
                        <input type="number" name="extra_price_per_meal" id="extra_price_per_meal"
                               class="pp-input @error('extra_price_per_meal') is-invalid @enderror"
                               value="{{ old('extra_price_per_meal') }}" step="0.001" min="0" placeholder="e.g. 0.650" required>
                        <span class="pp-suffix">KWD</span>
                    </div>
                    @error('extra_price_per_meal')
                        <div class="pp-error">{{ $message }}</div>
                    @else
                        <div class="pp-help">This amount is multiplied by (meals per day &times; delivery days) to get the total surcharge.</div>
                    @enderror
                </div>

                <div class="pp-group">
                    <div class="pp-switch-row">
                        <div>
                            <div class="pp-switch-label">Active</div>
                            <div class="pp-switch-sub">Visible to app when enabled</div>
                        </div>
                        <label class="pp-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <span class="pp-switch-track"></span>
                        </label>
                    </div>
                </div>

                <div class="pp-actions">
                    <button type="submit" class="pp-btn-submit"><i class="fas fa-plus"></i> Save Protein Option</button>
                    <a href="{{ route('admin.protein-options.index') }}" class="pp-btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
