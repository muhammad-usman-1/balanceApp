@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Add Branch</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.branches.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Branch Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label>Associated Areas</label>
                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                    @forelse($areas as $area)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="areas[]" value="{{ $area->id }}" id="area_{{ $area->id }}" {{ in_array($area->id, old('areas', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="area_{{ $area->id }}">
                                {{ $area->name }} <span class="text-muted">({{ number_format($area->delivery_charges, 2) }})</span>
                            </label>
                        </div>
                    @empty
                        <p class="text-muted">No available areas. All active areas are already assigned to branches.</p>
                    @endforelse
                </div>
                @error('areas')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Each area can only belong to one branch. Only unassigned areas are shown.</small>
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
            <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection
