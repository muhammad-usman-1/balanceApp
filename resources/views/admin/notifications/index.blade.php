@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Notifications</h3>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary float-right">Add Notification</a>
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
                    <th>Title</th>
                    <th>Message</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                <tr class="{{ $notification->trashed() ? 'table-secondary' : '' }}">
                    <td>{{ $notification->id }}</td>
                    <td>
                        <strong>{{ $notification->title }}</strong>
                        @if($notification->trashed())
                            <span class="badge badge-warning">Deleted</span>
                        @endif
                    </td>
                    <td>
                        <div style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ Str::limit($notification->message, 100) }}
                        </div>
                    </td>
                    <td>{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @if(!$notification->trashed())
                            <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" style="display:inline-block;">
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
                    <td colspan="5" class="text-center">No notifications found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

