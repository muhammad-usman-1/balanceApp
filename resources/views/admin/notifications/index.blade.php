@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-bell mr-2" style="color:#d97706;"></i> Notifications</h3>
        <a href="{{ route('admin.notifications.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Notification
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
                        <th>Title</th>
                        <th>Message</th>
                        <th>Sent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $notification->id }}</td>
                        <td>
                            <span style="font-weight:600;">{{ $notification->title }}</span>
                            @if($notification->trashed())
                                <span class="idx-chip chip-red ml-1">Deleted</span>
                            @endif
                        </td>
                        <td style="color:#6b7280;max-width:380px;">
                            {{ Str::limit($notification->message, 100) }}
                        </td>
                        <td style="color:#6b7280;font-size:.78rem;white-space:nowrap;">
                            {{ $notification->created_at->format('d M Y, H:i') }}
                        </td>
                        <td style="white-space:nowrap;">
                            @if(!$notification->trashed())
                                <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="idx-btn ib-edit">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                                </form>
                            @else
                                <span style="color:#9ca3af;font-size:.78rem;">Deleted</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="idx-empty"><i class="fas fa-bell-slash"></i><br>No notifications found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
