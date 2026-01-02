@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Pause/Resume Logs - Subscription #{{ $userSubcrption->id }}
    </div>

    <div class="card-body">
        <div class="mb-3">
            <strong>User:</strong> {{ $userSubcrption->user->name ?? 'N/A' }}<br>
            <strong>Subscription Plan:</strong> {{ $userSubcrption->subcrption_plans->title ?? 'N/A' }}<br>
            <strong>Current Status:</strong> 
            @if($userSubcrption->is_paused)
                <span class="badge badge-warning">Paused</span>
            @else
                <span class="badge badge-success">Active</span>
            @endif
            <br>
            <strong>Total Paused Days:</strong> {{ $userSubcrption->total_paused_days ?? 0 }}
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Timestamp</th>
                        <th>Paused At</th>
                        <th>Resumed At</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Performed By</th>
                        <th>Type</th>
                        <th>Notes</th>
                        <th>Metadata</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pauseLogs as $log)
                        <tr>
                            <td>
                                @if($log->action === 'pause')
                                    <span class="badge badge-warning">Pause</span>
                                @else
                                    <span class="badge badge-success">Resume</span>
                                @endif
                            </td>
                            <td>{{ $log->action_timestamp ? \Carbon\Carbon::parse($log->action_timestamp)->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $log->paused_at ? \Carbon\Carbon::parse($log->paused_at)->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $log->resumed_at ? \Carbon\Carbon::parse($log->resumed_at)->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $log->paused_days ?? '-' }}</td>
                            <td>{{ $log->reason ?? '-' }}</td>
                            <td>{{ $log->performed_by_name ?? 'N/A' }}</td>
                            <td>
                                @if($log->performed_by_type === 'admin')
                                    <span class="badge badge-primary">Admin</span>
                                @else
                                    <span class="badge badge-info">User</span>
                                @endif
                            </td>
                            <td>{{ $log->notes ?? '-' }}</td>
                            <td>
                                @if($log->metadata)
                                    <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#metadataModal{{ $log->id }}">
                                        View
                                    </button>
                                    <div class="modal fade" id="metadataModal{{ $log->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Metadata</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <pre>{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No pause/resume logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="form-group mt-3">
            <a class="btn btn-default" href="{{ route('admin.user-subcrptions.show', $userSubcrption->id) }}">
                Back to Subscription
            </a>
            <a class="btn btn-default" href="{{ route('admin.user-subcrptions.index') }}">
                Back to List
            </a>
        </div>
    </div>
</div>

@endsection

