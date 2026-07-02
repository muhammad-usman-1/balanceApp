@extends('layouts.admin')

@section('content')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="card">
    <div class="card-header">
        <h3>Add Area</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.areas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Area Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="delivery_charges">Delivery Charges <span class="text-danger">*</span></label>
                <input type="number" name="delivery_charges" id="delivery_charges" step="0.01" min="0" class="form-control @error('delivery_charges') is-invalid @enderror" value="{{ old('delivery_charges', 0) }}" required>
                @error('delivery_charges')
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
            <button type="submit" class="btn btn-success">Submit</button>
            <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection

