@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-tags mr-2" style="color:#7c3aed;"></i> Categories</h3>
        <a href="{{ route('admin.categories.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="idx-flash idx-flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $category->id }}</td>
                        <td>
                            <span style="font-weight:600;">{{ $category->name }}</span>
                            @if($category->trashed())
                                <span class="idx-chip chip-red ml-1">Deleted</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            @if(!$category->trashed())
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="idx-btn ib-edit">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                                </form>
                            @else
                                <form action="{{ route('admin.categories.restore', $category->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="idx-btn ib-green" onclick="return confirm('Restore this category?')">
                                        <i class="fas fa-undo"></i> Restore
                                    </button>
                                </form>
                                <form action="{{ route('admin.categories.force-delete', $category->id) }}" method="POST" style="display:inline-block;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="idx-btn ib-del"
                                            onclick="return confirm('Permanently delete? This removes the category from all meals.')">
                                        <i class="fas fa-times"></i> Force Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="idx-empty"><i class="fas fa-tags"></i><br>No categories found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
