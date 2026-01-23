@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.userSubcrption.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.user-subcrptions.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.id') }}
                        </th>
                        <td>
                            {{ $userSubcrption->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.selected_days') }}
                        </th>
                        <td>
                            {{ $userSubcrption->selected_days }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.start_date') }}
                        </th>
                        <td>
                            {{ $userSubcrption->start_date }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.end_date') }}
                        </th>
                        <td>
                            {{ $userSubcrption->end_date }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.user') }}
                        </th>
                        <td>
                            {{ $userSubcrption->user->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.subcrption_plans') }}
                        </th>
                        <td>
                            {{ $userSubcrption->subcrption_plans->title ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.duration') }}
                        </th>
                        <td>
                            {{ $userSubcrption->duration->title ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.price') }}
                        </th>
                        <td>
                            {{ number_format($userSubcrption->price, 3) }} KWD
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.payment') }}
                        </th>
                        <td>
                            {{ App\Models\UserSubcrption::PAYMENT_SELECT[$userSubcrption->payment] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.userSubcrption.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\UserSubcrption::STATUS_SELECT[$userSubcrption->status] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Pause Status
                        </th>
                        <td>
                            @if($userSubcrption->is_paused)
                                <span class="badge badge-warning">Paused</span>
                                <br>
                                <small>Paused at: {{ $userSubcrption->paused_at ? \Carbon\Carbon::parse($userSubcrption->paused_at)->format('Y-m-d H:i:s') : '-' }}</small>
                                <br>
                                <small>Paused until: {{ $userSubcrption->paused_until ? \Carbon\Carbon::parse($userSubcrption->paused_until)->format('Y-m-d H:i:s') : '-' }}</small>
                                <br>
                                <small>Total paused days: {{ $userSubcrption->total_paused_days ?? 0 }}</small>
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </td>
                    </tr>
                    @if($userSubcrption->original_end_date)
                    <tr>
                        <th>
                            Original End Date
                        </th>
                        <td>
                            {{ \Carbon\Carbon::parse($userSubcrption->original_end_date)->format(config('panel.date_format')) }}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
            
            <!-- Pause/Resume Actions -->
            <div class="form-group mt-4">
                @if(!$userSubcrption->is_paused && $userSubcrption->status === 'active')
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#pauseModal">
                        <i class="fas fa-pause"></i> Pause Subscription
                    </button>
                @elseif($userSubcrption->is_paused)
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Subscription is currently paused.</strong> 
                        You can resume it at any time, even if it was paused by mistake. The system will automatically adjust the end date.
                    </div>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#resumeModal">
                        <i class="fas fa-play"></i> Resume Subscription
                    </button>
                @endif
                
                @if($userSubcrption->pause_logs()->count() > 0)
                    <a href="{{ route('admin.user-subcrptions.pause-logs', $userSubcrption->id) }}" class="btn btn-info">
                        <i class="fas fa-history"></i> View Pause/Resume History ({{ $userSubcrption->pause_logs()->count() }})
                    </a>
                @endif
                
                <a class="btn btn-default" href="{{ route('admin.user-subcrptions.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Pause Modal -->
@if(!$userSubcrption->is_paused && $userSubcrption->status === 'active')
<div class="modal fade" id="pauseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pause Subscription</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.user-subcrptions.pause', $userSubcrption->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="days">Number of Days to Pause <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="days" name="days" min="1" max="365" required>
                        <small class="form-text text-muted">The subscription end date will be extended by this many days.</small>
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason for Pausing</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" maxlength="1000"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notes">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Pause Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Resume Modal -->
@if($userSubcrption->is_paused)
<div class="modal fade" id="resumeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resume Subscription</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.user-subcrptions.resume', $userSubcrption->id) }}" method="POST" id="resumeForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Subscription Pause Details:</strong><br>
                        <small>
                            Paused at: {{ $userSubcrption->paused_at ? \Carbon\Carbon::parse($userSubcrption->paused_at)->format('Y-m-d H:i:s') : 'N/A' }}<br>
                            Scheduled resume: {{ $userSubcrption->paused_until ? \Carbon\Carbon::parse($userSubcrption->paused_until)->format('Y-m-d H:i:s') : 'N/A' }}<br>
                            Total paused days: {{ $userSubcrption->total_paused_days ?? 0 }}
                        </small>
                    </div>
                    
                    <p><strong>Are you sure you want to resume this subscription?</strong></p>
                    
                    @php
                        $pausedAt = $userSubcrption->paused_at ? \Carbon\Carbon::parse($userSubcrption->paused_at) : null;
                        $pausedUntil = $userSubcrption->paused_until ? \Carbon\Carbon::parse($userSubcrption->paused_until) : null;
                        $now = \Carbon\Carbon::now();
                        $isEarlyResume = $pausedUntil && $now->lt($pausedUntil);
                    @endphp
                    
                    @if($isEarlyResume)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Early Resume:</strong> You are resuming this subscription before the scheduled resume date. 
                            The end date will be adjusted accordingly.
                        </div>
                    @endif
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="1000" placeholder="e.g., Mistaken pause, user requested immediate resume, etc."></textarea>
                        <small class="form-text text-muted">Add any notes about why the subscription is being resumed.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-play"></i> Resume Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif



@endsection