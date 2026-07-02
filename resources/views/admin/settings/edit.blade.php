@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')

<style>
.content-wrapper { background: #fff !important; }

/* iOS-style toggle — only custom piece not in idx-styles */
.st-switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
.st-switch input { opacity: 0; width: 0; height: 0; }
.st-slider {
    position: absolute; inset: 0; background: #d1d5db; border-radius: 24px;
    cursor: pointer; transition: background .2s;
}
.st-slider::before {
    content: ''; position: absolute;
    width: 18px; height: 18px; border-radius: 50%;
    background: #fff; left: 3px; bottom: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
    transition: transform .2s;
}
.st-switch input:checked + .st-slider { background: #16a34a; }
.st-switch input:checked + .st-slider::before { transform: translateX(20px); }

.pm-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px; border-bottom: 1px solid #f3f4f6;
}
.pm-row:last-child { border-bottom: none; }
.pm-row:hover { background: #fafafa; }
.pm-info { display: flex; align-items: center; gap: 12px; }
.pm-icon {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; font-size: .82rem; flex-shrink: 0;
}
</style>

@if(session('success'))
    <div class="idx-flash idx-flash-success" style="margin:0 0 16px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="idx-flash idx-flash-error" style="margin:0 0 16px;"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

{{-- ── Delivery Time Slots ── --}}
<div class="card idx-card" style="margin-bottom:20px;">
    <div class="card-header">
        <h3><i class="fas fa-clock mr-2" style="color:#2563eb;"></i> Delivery Time Slots</h3>
    </div>

    <div class="card-body">

        {{-- Add new slot form --}}
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb;">
            <div style="font-size:.8rem;font-weight:700;color:#374151;margin-bottom:12px;">
                <i class="fas fa-plus-circle mr-1" style="color:#16a34a;"></i> Add New Time Slot
            </div>
            <form action="{{ route('admin.settings.slots.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-md-0">
                            <label style="font-size:.78rem;font-weight:600;color:#374151;">
                                English Label <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="label_en"
                                   class="form-control @error('label_en') is-invalid @enderror"
                                   value="{{ old('label_en') }}"
                                   placeholder="e.g. 10:00 AM – 2:00 PM">
                            @error('label_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-md-0">
                            <label style="font-size:.78rem;font-weight:600;color:#374151;">Arabic Label</label>
                            <input type="text" name="label_ar"
                                   class="form-control @error('label_ar') is-invalid @enderror"
                                   value="{{ old('label_ar') }}"
                                   placeholder="e.g. ١٠:٠٠ ص – ٢:٠٠ م"
                                   dir="rtl">
                            @error('label_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-sm" style="height:38px;padding:0 20px;">
                            <i class="fas fa-plus mr-1"></i> Add Slot
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Slots table --}}
        <div class="table-responsive">
            <table class="table idx-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>English Label</th>
                        <th>Arabic Label</th>
                        <th>API Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slots as $slot)
                    {{-- View row --}}
                    <tr id="slot-view-{{ $slot->id }}">
                        <td style="color:#9ca3af;">{{ $loop->iteration }}</td>
                        <td style="font-weight:600;color:#111827;">{{ $slot->label_en }}</td>
                        <td style="direction:rtl;color:#374151;">{{ $slot->label_ar ?: '—' }}</td>
                        <td>
                            <span class="idx-chip chip-blue" style="font-family:monospace;letter-spacing:.02em;">
                                {{ $slot->value }}
                            </span>
                        </td>
                        <td style="white-space:nowrap;">
                            <button type="button" class="idx-btn ib-edit" onclick="toggleSlotEdit({{ $slot->id }})">
                                <i class="fas fa-pen"></i> Edit
                            </button>
                            <form action="{{ route('admin.settings.slots.destroy', $slot) }}" method="POST"
                                  onsubmit="return confirm('Delete this time slot?')" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    {{-- Inline edit row (hidden by default) --}}
                    <tr id="slot-edit-{{ $slot->id }}" style="display:none;background:#fffbeb;">
                        <td colspan="5" style="padding:12px 16px !important;">
                            <form action="{{ route('admin.settings.slots.update', $slot) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="row align-items-end" style="margin:0;gap:0;">
                                    <div class="col-md-4 pr-2">
                                        <label style="font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">
                                            English Label <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="label_en"
                                               class="form-control form-control-sm"
                                               value="{{ $slot->label_en }}" required>
                                    </div>
                                    <div class="col-md-4 pr-2">
                                        <label style="font-size:.75rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">
                                            Arabic Label
                                        </label>
                                        <input type="text" name="label_ar"
                                               class="form-control form-control-sm"
                                               value="{{ $slot->label_ar }}" dir="rtl">
                                    </div>
                                    <div class="col-md-4 d-flex" style="gap:6px;">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check mr-1"></i> Save
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                                onclick="toggleSlotEdit({{ $slot->id }})">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="idx-empty">
                            <i class="fas fa-clock"></i><br>No time slots yet. Add one above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- ── Payment Methods ── --}}
<div class="card idx-card" style="margin-bottom:20px;">
    <div class="card-header">
        <h3><i class="fas fa-credit-card mr-2" style="color:#b45309;"></i> Payment Methods</h3>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf @method('PUT')

        <div class="card-body" style="padding:0 !important;">

            <label class="pm-row" for="payment_knet" style="cursor:pointer;margin:0;">
                <div class="pm-info">
                    <div class="pm-icon" style="background:#e0f2fe;color:#0284c7;">
                        <i class="fas fa-university"></i>
                    </div>
                    <div>
                        <div style="font-size:.88rem;font-weight:600;color:#111827;">KNET</div>
                        <div style="font-size:.73rem;color:#9ca3af;">Kuwait electronic payment network</div>
                    </div>
                </div>
                <label class="st-switch">
                    <input type="checkbox" name="payment_knet" id="payment_knet" value="1"
                           {{ old('payment_knet', $setting->payment_knet) ? 'checked' : '' }}>
                    <span class="st-slider"></span>
                </label>
            </label>

            <label class="pm-row" for="payment_credit_card" style="cursor:pointer;margin:0;">
                <div class="pm-info">
                    <div class="pm-icon" style="background:#ede9fe;color:#5b21b6;">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <div style="font-size:.88rem;font-weight:600;color:#111827;">Credit / Debit Card</div>
                        <div style="font-size:.73rem;color:#9ca3af;">Visa, Mastercard accepted</div>
                    </div>
                </div>
                <label class="st-switch">
                    <input type="checkbox" name="payment_credit_card" id="payment_credit_card" value="1"
                           {{ old('payment_credit_card', $setting->payment_credit_card) ? 'checked' : '' }}>
                    <span class="st-slider"></span>
                </label>
            </label>

            <label class="pm-row" for="payment_cash" style="cursor:pointer;margin:0;">
                <div class="pm-info">
                    <div class="pm-icon" style="background:#dcfce7;color:#15803d;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <div style="font-size:.88rem;font-weight:600;color:#111827;">Cash on Delivery</div>
                        <div style="font-size:.73rem;color:#9ca3af;">Pay when your order arrives</div>
                    </div>
                </div>
                <label class="st-switch">
                    <input type="checkbox" name="payment_cash" id="payment_cash" value="1"
                           {{ old('payment_cash', $setting->payment_cash) ? 'checked' : '' }}>
                    <span class="st-slider"></span>
                </label>
            </label>

        </div>

        <div style="padding:14px 20px;border-top:1px solid #e5e7eb;background:#f9fafb;border-radius:0 0 14px 14px;display:flex;gap:10px;">
            <button type="submit" class="btn btn-success btn-sm" style="padding:7px 22px;">
                <i class="fas fa-check mr-1"></i> Save Payment Methods
            </button>
            <a href="{{ route('admin.home') }}" class="btn btn-secondary btn-sm" style="padding:7px 18px;">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function toggleSlotEdit(id) {
    var view = document.getElementById('slot-view-' + id);
    var edit = document.getElementById('slot-edit-' + id);
    var hidden = edit.style.display === 'none';
    edit.style.display = hidden ? 'table-row' : 'none';
    view.style.background = hidden ? '#fffbeb' : '';
}
</script>
@endsection
