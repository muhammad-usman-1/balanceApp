@extends('layouts.admin')

@section('content')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Affiliate Code Usage Logs - {{ $affiliatedCode->code }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.affiliated-codes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h5>Code Information</h5>
                    <p><strong>Full Name:</strong> {{ $affiliatedCode->full_name }}</p>
                    <p><strong>Code:</strong> <span class="text-primary">{{ $affiliatedCode->code }}</span></p>
                    <p><strong>Total Usage:</strong> <span class="badge badge-primary">{{ $affiliatedCode->users->count() }}</span></p>
                </div>

                @if($affiliatedCode->users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Date & Time Used</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($affiliatedCode->users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name ?? 'N/A' }}</td>
                                <td>{{ $user->email ?? 'N/A' }}</td>
                                <td>{{ $user->mobile ?? 'N/A' }}</td>
                                <td>
                                    <strong>{{ $user->created_at->format('Y-m-d') }}</strong><br>
                                    <small class="text-muted">{{ $user->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info" title="View User">
                                        <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No users have used this affiliate code yet.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

