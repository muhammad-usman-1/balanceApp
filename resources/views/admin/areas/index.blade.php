@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Areas</h3>
        <a href="{{ route('admin.areas.create') }}" class="btn btn-primary float-right">Add Area</a>
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
                    <th>Area Name</th>
                    <th>Delivery Charges</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($areas as $area)
                <tr>
                    <td>{{ $area->id }}</td>
                    <td>{{ $area->name }}</td>
                    <td>{{ number_format($area->delivery_charges, 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $area->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($area->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.areas.edit', $area->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.areas.destroy', $area->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No areas found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
