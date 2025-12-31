@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Branches</h3>
        <a href="{{ route('admin.branches.create') }}" class="btn btn-primary float-right">Add Branch</a>
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
                    <th>Branch Name</th>
                    <th>Associated Areas</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr class="{{ $branch->trashed() ? 'table-secondary' : '' }}">
                    <td>{{ $branch->id }}</td>
                    <td>
                        {{ $branch->name }}
                        @if($branch->trashed())
                            <span class="badge badge-warning">Deleted</span>
                        @endif
                    </td>
                    <td>
                        @if($branch->areas->count() > 0)
                            @foreach($branch->areas as $area)
                                <span class="badge badge-info">{{ $area->name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">No areas assigned</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $branch->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($branch->status) }}
                        </span>
                    </td>
                    <td>
                        @if(!$branch->trashed())
                            <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        @else
                            <span class="text-muted">Deleted</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No branches found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

