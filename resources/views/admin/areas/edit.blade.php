@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Edit Area</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.areas.update', $area->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Area Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $area->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="delivery_charges">Delivery Charges <span class="text-danger">*</span></label>
                <input type="number" name="delivery_charges" id="delivery_charges" step="0.01" min="0" class="form-control @error('delivery_charges') is-invalid @enderror" value="{{ old('delivery_charges', $area->delivery_charges) }}" required>
                @error('delivery_charges')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="status">Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="active" {{ old('status', $area->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $area->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection

