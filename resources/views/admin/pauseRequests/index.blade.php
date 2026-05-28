@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-pause-circle mr-2" style="color:#d97706;"></i> Pause Requests</h3>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div style="margin:16px 20px 0; padding:10px 14px; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; border-radius:8px; font-size:.83rem;">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="margin:16px 20px 0; padding:10px 14px; background:#fee2e2; color:#dc2626; border:1px solid #fecaca; border-radius:8px; font-size:.83rem;">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Status filter tabs --}}
    <div style="padding:16px 20px 0; display:flex; gap:8px; flex-wrap:wrap;">
        @foreach(['pending' => ['#d97706','#fef3c7'], 'approved' => ['#16a34a','#dcfce7'], 'rejected' => ['#dc2626','#fee2e2'], 'all' => ['#4b5563','#f3f4f6']] as $tab => $colors)
        <a href="{{ route('admin.pause-requests.index', ['status' => $tab]) }}"
           style="padding:6px 14px; border-radius:20px; font-size:.78rem; font-weight:600; text-decoration:none;
                  background:{{ $status === $tab ? $colors[1] : '#f9fafb' }};
                  color:{{ $status === $tab ? $colors[0] : '#6b7280' }};
                  border:1px solid {{ $status === $tab ? $colors[0].'33' : '#e5e7eb' }};">
            {{ ucfirst($tab) }}
            @if($tab !== 'all')
                <span style="margin-left:4px; background:{{ $colors[0] }}22; color:{{ $colors[0] }}; border-radius:10px; padding:1px 7px; font-size:.72rem;">
                    {{ $counts[$tab] }}
                </span>
            @endif
        </a>
        @endforeach
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table datatable" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th>Pause From</th>
                        <th>Pause To</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Requested</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pauseRequests as $pr)
                    <tr>
                        <td style="font-weight:600; color:#111827;">{{ $pr->id }}</td>
                        <td>
                            <div style="font-weight:600; font-size:.83rem;">{{ $pr->user->name ?? '—' }}</div>
                            <div style="font-size:.72rem; color:#9ca3af;">{{ $pr->user->mobile ?? '' }}</div>
                        </td>
                        <td style="font-size:.83rem;">{{ $pr->subscription->subcrption_plans->title ?? '—' }}</td>
                        <td style="white-space:nowrap; font-size:.83rem;">{{ $pr->pause_start_date?->format('Y-m-d') }}</td>
                        <td style="white-space:nowrap; font-size:.83rem;">{{ $pr->pause_end_date?->format('Y-m-d') }}</td>
                        <td style="font-weight:600; text-align:center;">{{ $pr->pause_days }}</td>
                        <td style="max-width:200px; font-size:.8rem; color:#374151;">
                            {{ Str::limit($pr->reason, 80) }}
                        </td>
                        <td style="white-space:nowrap; font-size:.78rem; color:#6b7280;">
                            {{ $pr->created_at?->format('Y-m-d') }}
                        </td>
                        <td>
                            @if($pr->status === 'pending')
                                <span class="idx-chip chip-orange"><i class="fas fa-clock" style="font-size:.5rem;"></i> Pending</span>
                            @elseif($pr->status === 'approved')
                                <span class="idx-chip chip-green"><i class="fas fa-check" style="font-size:.5rem;"></i> Approved</span>
                            @else
                                <span class="idx-chip" style="background:#fee2e2;color:#dc2626;border-color:#fecaca;">
                                    <i class="fas fa-times" style="font-size:.5rem;"></i> Rejected
                                </span>
                            @endif
                            @if($pr->admin_notes)
                                <div style="font-size:.7rem; color:#9ca3af; margin-top:2px;" title="{{ $pr->admin_notes }}">
                                    <i class="fas fa-comment-alt"></i> {{ Str::limit($pr->admin_notes, 40) }}
                                </div>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            @if($pr->status === 'pending')
                                {{-- Approve --}}
                                <button type="button" class="idx-btn ib-green"
                                        data-toggle="modal" data-target="#approveModal{{ $pr->id }}">
                                    <i class="fas fa-check"></i> Approve
                                </button>

                                {{-- Reject --}}
                                <button type="button" class="idx-btn ib-del"
                                        data-toggle="modal" data-target="#rejectModal{{ $pr->id }}">
                                    <i class="fas fa-times"></i> Reject
                                </button>

                                {{-- Approve Modal --}}
                                <div class="modal fade" id="approveModal{{ $pr->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.pause-requests.approve', $pr->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="fas fa-check-circle text-success mr-2"></i>Approve Pause Request</h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px; margin-bottom:14px; font-size:.83rem;">
                                                        <strong>{{ $pr->user->name ?? '—' }}</strong><br>
                                                        Plan: {{ $pr->subscription->subcrption_plans->title ?? '—' }}<br>
                                                        Pause: <strong>{{ $pr->pause_start_date?->format('Y-m-d') }}</strong>
                                                               → <strong>{{ $pr->pause_end_date?->format('Y-m-d') }}</strong>
                                                               ({{ $pr->pause_days }} days)<br>
                                                        Reason: {{ $pr->reason }}
                                                    </div>
                                                    <p style="font-size:.83rem; color:#374151;">
                                                        Approving will pause the subscription and extend its end date by <strong>{{ $pr->pause_days }} day(s)</strong>.
                                                    </p>
                                                    <div class="form-group">
                                                        <label style="font-size:.82rem; font-weight:600;">Note to customer (optional)</label>
                                                        <textarea name="admin_notes" class="form-control" rows="2"
                                                                  placeholder="e.g. Approved, your subscription has been paused." style="font-size:.83rem;"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check mr-1"></i>Confirm Approve</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reject Modal --}}
                                <div class="modal fade" id="rejectModal{{ $pr->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.pause-requests.reject', $pr->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="fas fa-times-circle text-danger mr-2"></i>Reject Pause Request</h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:12px; margin-bottom:14px; font-size:.83rem;">
                                                        <strong>{{ $pr->user->name ?? '—' }}</strong> —
                                                        {{ $pr->pause_start_date?->format('Y-m-d') }} → {{ $pr->pause_end_date?->format('Y-m-d') }}
                                                        ({{ $pr->pause_days }} days)
                                                    </div>
                                                    <div class="form-group">
                                                        <label style="font-size:.82rem; font-weight:600;">Reason for rejection (optional)</label>
                                                        <textarea name="admin_notes" class="form-control" rows="2"
                                                                  placeholder="e.g. Request period conflicts with active deliveries." style="font-size:.83rem;"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times mr-1"></i>Confirm Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @else
                                <span style="font-size:.75rem; color:#9ca3af;">
                                    Reviewed by {{ $pr->reviewer->name ?? '—' }}<br>
                                    {{ $pr->reviewed_at?->format('Y-m-d') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="text-align:center; color:#9ca3af; padding:32px; font-size:.85rem;">
                            <i class="fas fa-inbox fa-2x" style="display:block; margin-bottom:8px;"></i>
                            No {{ $status !== 'all' ? $status : '' }} pause requests found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
@section('scripts')
@parent
<script>
$(function () {
    $.extend(true, $.fn.dataTable.defaults, { orderCellsTop: true, order: [[0, 'desc']], pageLength: 25 });
    $('.datatable:not(.ajaxTable)').DataTable({
        buttons: [],
        columnDefs: [{ orderable: false, targets: -1 }],
        select: false
    });
});
</script>
@endsection
