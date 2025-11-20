@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Categories</h3>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary float-right">Add Category</a>
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
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr class="{{ $category->trashed() ? 'table-secondary' : '' }}">
                    <td>{{ $category->id }}</td>
                    <td>
                        {{ $category->name }}
                        @if($category->trashed())
                            <span class="badge badge-warning">Deleted</span>
                        @endif
                    </td>
                    <td>
                        @if(!$category->trashed())
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        @else
                            <form action="{{ route('admin.categories.restore', $category->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Restore this category?')">Restore</button>
                            </form>
                            <form action="{{ route('admin.categories.force-delete', $category->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Permanently delete this category? This will remove category from all meals.')">Permanently Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
