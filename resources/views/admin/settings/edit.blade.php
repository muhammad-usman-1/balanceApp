@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Settings</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Delivery Time Slots Section -->
            <div class="settings-section mb-4">
                <h5 class="section-title mb-3">
                    <i class="fas fa-clock"></i> Delivery Time Slots
                </h5>
                
                <div class="form-group">
                    <label for="delivery_time_slot_1_en">Delivery Time Slot 1 English <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="delivery_time_slot_1_en" 
                           id="delivery_time_slot_1_en" 
                           class="form-control @error('delivery_time_slot_1_en') is-invalid @enderror" 
                           value="{{ old('delivery_time_slot_1_en', $setting->delivery_time_slot_1_en) }}" 
                           required>
                    @error('delivery_time_slot_1_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="delivery_time_slot_2_en">Delivery Time Slot 2 English <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="delivery_time_slot_2_en" 
                           id="delivery_time_slot_2_en" 
                           class="form-control @error('delivery_time_slot_2_en') is-invalid @enderror" 
                           value="{{ old('delivery_time_slot_2_en', $setting->delivery_time_slot_2_en) }}" 
                           required>
                    @error('delivery_time_slot_2_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Payment Methods Section -->
            <div class="settings-section mb-4">
                <h5 class="section-title mb-3">
                    <i class="fas fa-credit-card"></i> Payment Methods
                </h5>
                
                <div class="payment-methods-list">
                    <div class="payment-method-item">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="payment_knet" 
                                   id="payment_knet" 
                                   value="1"
                                   {{ old('payment_knet', $setting->payment_knet) ? 'checked' : '' }}>
                            <label class="form-check-label" for="payment_knet">
                                Knet
                            </label>
                        </div>
                    </div>

                    <div class="payment-method-item">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="payment_credit_card" 
                                   id="payment_credit_card" 
                                   value="1"
                                   {{ old('payment_credit_card', $setting->payment_credit_card) ? 'checked' : '' }}>
                            <label class="form-check-label" for="payment_credit_card">
                                Credit Card
                            </label>
                        </div>
                    </div>

                    <div class="payment-method-item">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="payment_cash" 
                                   id="payment_cash" 
                                   value="1"
                                   {{ old('payment_cash', $setting->payment_cash) ? 'checked' : '' }}>
                            <label class="form-check-label" for="payment_cash">
                                Cash
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Update
                </button>
                <a href="{{ route('admin.home') }}" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<style>
.settings-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.section-title {
    color: #495057;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #007bff;
}

.payment-methods-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.payment-method-item {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 0;
    margin-bottom: 12px;
    transition: all 0.3s ease;
}

.payment-method-item:last-child {
    margin-bottom: 0;
}

.payment-method-item:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
}

.payment-method-item .form-check {
    margin: 0;
    padding: 12px 15px;
    display: flex;
    align-items: center;
    width: 100%;
}

.payment-method-item .form-check-input {
    width: 20px;
    height: 20px;
    margin: 0;
    margin-right: 12px;
    cursor: pointer;
    flex-shrink: 0;
    border: 2px solid #6c757d;
    border-radius: 4px;
    background-color: white;
    position: relative;
}

.payment-method-item .form-check-input:checked {
    background-color: #6c757d;
    border-color: #6c757d;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
    background-size: 100% 100%;
    background-position: center;
    background-repeat: no-repeat;
}

.payment-method-item .form-check-input:focus {
    border-color: #6c757d;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
}

.payment-method-item .form-check-label {
    font-weight: 500;
    color: #495057;
    cursor: pointer;
    margin: 0;
    padding: 0;
    display: inline-block;
    line-height: 20px;
    vertical-align: middle;
}
</style>
@endsection

