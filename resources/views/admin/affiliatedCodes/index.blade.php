@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Affiliated Codes</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.affiliated-codes.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Affiliated Code
                    </a>
                </div>
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
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Code</th>
                                <th>Gift Type</th>
                                <th>Gift Value</th>
                                <th>Status</th>
                                <th>Usage Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($affiliatedCodes as $code)
                            <tr>
                                <td>{{ $code->id }}</td>
                                <td>{{ $code->full_name }}</td>
                                <td>
                                    <strong class="text-primary">{{ $code->code }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($code->gift_type) }}</span>
                                </td>
                                <td>
                                    @if($code->gift_type === 'percentage')
                                        {{ number_format($code->gift_value, 2) }}%
                                    @else
                                        {{ number_format($code->gift_value, 2) }}
                                    @endif
                                </td>
                                <td>
                                    @if($code->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $code->users_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.affiliated-codes.show', $code->id) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                                        </a>
                                        <a href="{{ route('admin.affiliated-codes.edit', $code->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                                        </a>
                                        <form action="{{ route('admin.affiliated-codes.destroy', $code->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this affiliated code?')" title="Delete">
                                                <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No affiliated codes found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

