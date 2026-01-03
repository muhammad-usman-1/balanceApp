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

});
</script>
@endsection

