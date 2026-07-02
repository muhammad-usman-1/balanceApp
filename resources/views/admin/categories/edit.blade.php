@extends('layouts.admin')

@section('content')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="card">
    <div class="card-header">
        <h3>Edit Category</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $category->name }}" required>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection
