@extends('layouts.admin')

@section('content')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="card">
    <div class="card-header">
        <h3>Add Notification</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.notifications.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="title">Notification Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="message">Message <span class="text-danger">*</span></label>
                <textarea name="message" id="message" rows="5" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-success">Submit</button>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection

