@extends('layouts.admin')
@section('content')

<style>
.st-page { max-width: 760px; }

.st-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05); margin-bottom: 20px; overflow: hidden;
}
.st-card-header {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 22px; background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}
.st-card-icon {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem; flex-shrink: 0;
}
.st-card-title { font-size: .88rem; font-weight: 700; color: #111827; }
.st-card-body  { padding: 20px 22px; }

/* ── Field ── */
.st-field { margin-bottom: 18px; }
.st-field:last-child { margin-bottom: 0; }
.st-label {
    display: block; font-size: .78rem; font-weight: 600;
    color: #374151; margin-bottom: 6px; letter-spacing: .01em;
}
.st-label .req { color: #ef4444; margin-left: 2px; }
.st-input {
    width: 100%; padding: 9px 13px;
    border: 1.5px solid #e5e7eb; border-radius: 9px;
    font-size: .88rem; color: #111827; font-family: inherit;
    background: #fff; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.st-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22,163,74,.1);
}
.st-input.is-invalid { border-color: #ef4444; }
.st-invalid { font-size: .75rem; color: #ef4444; margin-top: 4px; }

/* ── Toggle switch ── */
.st-toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 16px; background: #f9fafb;
    border: 1px solid #f3f4f6; border-radius: 10px; margin-bottom: 10px;
    transition: border-color .15s, background .15s;
    cursor: pointer;
}
.st-toggle-row:last-child { margin-bottom: 0; }
.st-toggle-row:hover { background: #f1f5f9; border-color: #e2e8f0; }
.st-toggle-info { display: flex; align-items: center; gap: 12px; }
.st-toggle-icon {
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0;
}
.st-toggle-label { font-size: .85rem; font-weight: 600; color: #111827; }
.st-toggle-sub   { font-size: .72rem; color: #9ca3af; margin-top: 1px; }

/* iOS-style toggle */
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

/* ── Action bar ── */
.st-actions {
    display: flex; align-items: center; gap: 10px;
    padding: 16px 22px; background: #f9fafb;
    border-top: 1px solid #e5e7eb; border-radius: 0 0 14px 14px;
}
.st-btn-save {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 22px; background: linear-gradient(135deg,#16a34a,#15803d);
    color: #fff; border: none; border-radius: 9px;
    font-size: .85rem; font-weight: 600; font-family: inherit; cursor: pointer;
    box-shadow: 0 3px 10px rgba(22,163,74,.25);
    transition: opacity .15s, transform .12s;
}
.st-btn-save:hover { opacity: .9; transform: translateY(-1px); }
.st-btn-back {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px; background: #f3f4f6; color: #374151;
    border: none; border-radius: 9px; font-size: .85rem; font-weight: 600;
    font-family: inherit; cursor: pointer; text-decoration: none;
    transition: background .15s;
}
.st-btn-back:hover { background: #e5e7eb; text-decoration: none; color: #374151; }

.st-flash {
    padding: 10px 14px; border-radius: 8px; font-size: .83rem; margin-bottom: 18px;
    display: flex; align-items: center; gap: 8px;
}
.st-flash-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.st-flash-error   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

/* ── Slot list ── */
.slot-row {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 14px; background: #f9fafb;
    border: 1px solid #f3f4f6; border-radius: 10px; margin-bottom: 8px;
}
.slot-row:last-child { margin-bottom: 0; }
.slot-value {
    font-size: .72rem; color: #9ca3af; font-family: monospace;
    background: #f3f4f6; padding: 2px 7px; border-radius: 5px; flex-shrink: 0;
}
.slot-labels { flex: 1; }
.slot-label-en { font-size: .85rem; font-weight: 600; color: #111827; }
.slot-label-ar { font-size: .78rem; color: #6b7280; direction: rtl; }
.slot-delete {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 7px;
    background: #fee2e2; color: #dc2626; border: none; cursor: pointer;
    font-size: .8rem; flex-shrink: 0; transition: background .15s;
}
.slot-delete:hover { background: #fecaca; }

/* ── Add slot form ── */
.add-slot-form {
    margin-top: 16px; padding: 16px;
    background: #f0fdf4; border: 1.5px dashed #86efac; border-radius: 10px;
}
.add-slot-form .add-slot-title {
    font-size: .8rem; font-weight: 700; color: #15803d; margin-bottom: 12px;
    display: flex; align-items: center; gap: 6px;
}
.add-slot-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
@media(max-width:520px) { .add-slot-grid { grid-template-columns: 1fr; } }
.st-btn-add {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; background: #16a34a; color: #fff;
    border: none; border-radius: 9px; font-size: .83rem; font-weight: 600;
    font-family: inherit; cursor: pointer; margin-top: 10px;
    transition: opacity .15s;
}
.st-btn-add:hover { opacity: .88; }
</style>

<div class="st-page">

    {{-- Page heading --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <div style="width:42px;height:42px;border-radius:11px;background:linear-gradient(135deg,#475569,#334155);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.95rem;">
            <i class="fas fa-cog"></i>
        </div>
        <div>
            <div style="font-size:1.1rem;font-weight:700;color:#111827;">Settings</div>
            <div style="font-size:.78rem;color:#9ca3af;">System configuration</div>
        </div>
    </div>

    @if(session('success'))
        <div class="st-flash st-flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="st-flash st-flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- ── Delivery Time Slots ── --}}
    <div class="st-card">
        <div class="st-card-header">
            <div class="st-card-icon" style="background:#eff6ff;color:#2563eb;">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div class="st-card-title">Delivery Time Slots</div>
                <div style="font-size:.72rem;color:#9ca3af;margin-top:1px;">
                    Shown in the app checkout. App sends the <strong>value</strong> key to backend.
                </div>
            </div>
        </div>
        <div class="st-card-body">

            {{-- Existing slots --}}
            @forelse($slots as $slot)
                <div class="slot-row">
                    <div class="slot-labels">
                        <div class="slot-label-en">{{ $slot->label_en }}</div>
                        @if($slot->label_ar)
                            <div class="slot-label-ar">{{ $slot->label_ar }}</div>
                        @endif
                    </div>
                    <span class="slot-value">{{ $slot->value }}</span>
                    <form action="{{ route('admin.settings.slots.destroy', $slot) }}" method="POST"
                          onsubmit="return confirm('Delete this time slot?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="slot-delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div style="text-align:center;padding:20px;color:#9ca3af;font-size:.83rem;">
                    No time slots yet. Add one below.
                </div>
            @endforelse

            {{-- Add new slot --}}
            <div class="add-slot-form">
                <div class="add-slot-title">
                    <i class="fas fa-plus-circle"></i> Add New Time Slot
                </div>
                <form action="{{ route('admin.settings.slots.store') }}" method="POST">
                    @csrf
                    <div class="add-slot-grid">
                        <div>
                            <label class="st-label" for="label_en">
                                English Label <span class="req">*</span>
                            </label>
                            <input type="text" name="label_en" id="label_en"
                                   class="st-input @error('label_en') is-invalid @enderror"
                                   value="{{ old('label_en') }}"
                                   placeholder="e.g. 10:00 AM – 2:00 PM">
                            @error('label_en')
                                <div class="st-invalid">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="st-label" for="label_ar">Arabic Label</label>
                            <input type="text" name="label_ar" id="label_ar"
                                   class="st-input @error('label_ar') is-invalid @enderror"
                                   value="{{ old('label_ar') }}"
                                   placeholder="e.g. ١٠:٠٠ ص – ٢:٠٠ م"
                                   dir="rtl">
                            @error('label_ar')
                                <div class="st-invalid">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="st-btn-add">
                        <i class="fas fa-plus"></i> Add Slot
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- ── Payment Methods ── --}}
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="st-card">
            <div class="st-card-header">
                <div class="st-card-icon" style="background:#fef3c7;color:#b45309;">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div>
                    <div class="st-card-title">Payment Methods</div>
                </div>
            </div>
            <div class="st-card-body">

                <label class="st-toggle-row" for="payment_knet">
                    <div class="st-toggle-info">
                        <div class="st-toggle-icon" style="background:#e0f2fe;color:#0284c7;">
                            <i class="fas fa-university"></i>
                        </div>
                        <div>
                            <div class="st-toggle-label">Knet</div>
                            <div class="st-toggle-sub">Kuwait electronic payment network</div>
                        </div>
                    </div>
                    <label class="st-switch">
                        <input type="checkbox" name="payment_knet" id="payment_knet" value="1"
                               {{ old('payment_knet', $setting->payment_knet) ? 'checked' : '' }}>
                        <span class="st-slider"></span>
                    </label>
                </label>

                <label class="st-toggle-row" for="payment_credit_card">
                    <div class="st-toggle-info">
                        <div class="st-toggle-icon" style="background:#ede9fe;color:#5b21b6;">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div>
                            <div class="st-toggle-label">Credit Card</div>
                            <div class="st-toggle-sub">Visa, Mastercard, etc.</div>
                        </div>
                    </div>
                    <label class="st-switch">
                        <input type="checkbox" name="payment_credit_card" id="payment_credit_card" value="1"
                               {{ old('payment_credit_card', $setting->payment_credit_card) ? 'checked' : '' }}>
                        <span class="st-slider"></span>
                    </label>
                </label>

                <label class="st-toggle-row" for="payment_cash">
                    <div class="st-toggle-info">
                        <div class="st-toggle-icon" style="background:#dcfce7;color:#15803d;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <div class="st-toggle-label">Cash</div>
                            <div class="st-toggle-sub">Pay on delivery</div>
                        </div>
                    </div>
                    <label class="st-switch">
                        <input type="checkbox" name="payment_cash" id="payment_cash" value="1"
                               {{ old('payment_cash', $setting->payment_cash) ? 'checked' : '' }}>
                        <span class="st-slider"></span>
                    </label>
                </label>

            </div>
        </div>

        {{-- ── Actions ── --}}
        <div class="st-actions" style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;margin-top:4px;">
            <button type="submit" class="st-btn-save">
                <i class="fas fa-check"></i> Save Settings
            </button>
            <a href="{{ route('admin.home') }}" class="st-btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

    </form>
</div>

@endsection
