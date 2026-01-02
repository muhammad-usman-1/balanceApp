@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Affiliated Code</h3>
            </div>
            <div class="card-body">
        <form action="{{ route('admin.affiliated-codes.update', $affiliatedCode->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="full_name">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                       id="full_name" name="full_name" value="{{ old('full_name', $affiliatedCode->full_name) }}" required>
                @error('full_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="code">Affiliated Code <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                           id="code" name="code" value="{{ old('code', $affiliatedCode->code) }}" 
                           required maxlength="50">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-secondary" id="generateCodeBtn">
                            <i class="fas fa-magic"></i> <span class="d-none d-md-inline">Auto-Generate</span>
                        </button>
                    </div>
                </div>
                @error('code')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="gift_type">Gift Type <span class="text-danger">*</span></label>
                <select class="form-control @error('gift_type') is-invalid @enderror" 
                        id="gift_type" name="gift_type" required>
                    <option value="">Select Gift Type</option>
                    <option value="percentage" {{ old('gift_type', $affiliatedCode->gift_type) === 'percentage' ? 'selected' : '' }}>Percentage</option>
                    <option value="fixed" {{ old('gift_type', $affiliatedCode->gift_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                </select>
                @error('gift_type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="gift_value">Gift Value <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" 
                           class="form-control @error('gift_value') is-invalid @enderror" 
                           id="gift_value" name="gift_value" value="{{ old('gift_value', $affiliatedCode->gift_value) }}" required>
                    <div class="input-group-append">
                        <span class="input-group-text" id="gift_type_suffix">
                            {{ $affiliatedCode->gift_type === 'percentage' ? '%' : '' }}
                        </span>
                    </div>
                </div>
                <small class="form-text text-muted" id="gift_value_help">
                    @if($affiliatedCode->gift_type === 'percentage')
                        Enter percentage value (0-100)
                    @else
                        Enter fixed amount value
                    @endif
                </small>
                @error('gift_value')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $affiliatedCode->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
                <small class="form-text text-muted">Check this box to make the affiliated code active and available for use.</small>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes', $affiliatedCode->notes) }}</textarea>
                @error('notes')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Affiliated Code
                </button>
                <a href="{{ route('admin.affiliated-codes.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-generate code button
    $('#generateCodeBtn').on('click', function() {
        var fullName = $('#full_name').val();
        if (!fullName) {
            alert('Please enter a full name first.');
            return;
        }

        $.ajax({
            url: '{{ route("admin.affiliated-codes.generate-code") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                full_name: fullName
            },
            success: function(response) {
                if (response.success) {
                    $('#code').val(response.code);
                }
            },
            error: function() {
                alert('Error generating code. Please try again.');
            }
        });
    });

    // Update gift type suffix and help text
    $('#gift_type').on('change', function() {
        var type = $(this).val();
        var suffix = $('#gift_type_suffix');
        var help = $('#gift_value_help');
        
        if (type === 'percentage') {
            suffix.text('%');
            help.text('Enter percentage value (0-100)');
            $('#gift_value').attr('max', '100');
        } else if (type === 'fixed') {
            suffix.text('');
            help.text('Enter fixed amount value');
            $('#gift_value').removeAttr('max');
        }
    });

    // Trigger on page load
    $('#gift_type').trigger('change');
});
</script>
@endsection

