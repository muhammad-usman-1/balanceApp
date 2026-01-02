@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Affiliated Code Details</h3>
            </div>
            <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="{{ route('admin.affiliated-codes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('admin.affiliated-codes.edit', $affiliatedCode->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>

        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th width="200">ID</th>
                    <td>{{ $affiliatedCode->id }}</td>
                </tr>
                <tr>
                    <th>Full Name</th>
                    <td>{{ $affiliatedCode->full_name }}</td>
                </tr>
                <tr>
                    <th>Code</th>
                    <td><strong class="text-primary">{{ $affiliatedCode->code }}</strong></td>
                </tr>
                <tr>
                    <th>Gift Type</th>
                    <td>
                        <span class="badge badge-info">{{ ucfirst($affiliatedCode->gift_type) }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Gift Value</th>
                    <td>
                        @if($affiliatedCode->gift_type === 'percentage')
                            {{ number_format($affiliatedCode->gift_value, 2) }}%
                        @else
                            {{ number_format($affiliatedCode->gift_value, 2) }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if($affiliatedCode->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Usage Count</th>
                    <td>
                        <span class="badge badge-primary">{{ $affiliatedCode->usage_count }}</span>
                    </td>
                </tr>
                @if($affiliatedCode->notes)
                <tr>
                    <th>Notes</th>
                    <td>{{ $affiliatedCode->notes }}</td>
                </tr>
                @endif
                <tr>
                    <th>Created At</th>
                    <td>{{ $affiliatedCode->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
                <tr>
                    <th>Updated At</th>
                    <td>{{ $affiliatedCode->updated_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            </tbody>
        </table>

                @if($affiliatedCode->users->count() > 0)
                <div class="mt-4">
                    <h4>Users Registered with This Code ({{ $affiliatedCode->users->count() }})</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Registered At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($affiliatedCode->users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->mobile }}</td>
                                    <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="alert alert-info mt-4">
                    No users have registered with this affiliated code yet.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

