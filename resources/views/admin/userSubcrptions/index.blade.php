@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-clipboard-list mr-2" style="color:#16a34a;"></i> Subscriptions</h3>
        @can('user_subcrption_create')
        <a href="{{ route('admin.user-subcrptions.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Subscription
        </a>
        @endcan
    </div>

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

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table datatable datatable-UserSubcrption" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Pause</th>
                        <th>Personalized</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userSubcrptions as $sub)
                    <tr data-entry-id="{{ $sub->id }}">
                        <td style="font-weight:600; color:#111827;">#{{ $sub->id }}</td>
                        <td>{{ $sub->user->name ?? '—' }}</td>
                        <td>{{ $sub->subcrption_plans->title ?? '—' }}</td>
                        <td style="white-space:nowrap;">{{ $sub->start_date ?? '—' }}</td>
                        <td style="white-space:nowrap;">{{ $sub->end_date ?? '—' }}</td>
                        <td>
                            @if($sub->payment === 'paid')
                                <span class="idx-chip chip-blue"><i class="fas fa-check" style="font-size:.55rem;"></i> Paid</span>
                            @else
                                <span class="idx-chip chip-orange"><i class="fas fa-clock" style="font-size:.55rem;"></i> {{ ucfirst($sub->payment ?? 'pending') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($sub->status === 'active')
                                <span class="idx-chip chip-green"><i class="fas fa-circle" style="font-size:.4rem;"></i> Active</span>
                            @else
                                <span class="idx-chip chip-gray">{{ ucfirst($sub->status ?? 'inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($sub->is_paused)
                                <span class="idx-chip chip-yellow"><i class="fas fa-pause" style="font-size:.55rem;"></i> Paused</span>
                                <div style="font-size:.7rem; color:#9ca3af; margin-top:2px;">{{ $sub->total_paused_days ?? 0 }} days</div>
                            @else
                                <span style="font-size:.78rem; color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($sub->is_personalized)
                                <span class="idx-chip chip-blue">Yes</span>
                            @else
                                <span style="font-size:.78rem; color:#9ca3af;">No</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            @can('user_subcrption_show')
                            <a href="{{ route('admin.user-subcrptions.show', $sub->id) }}" class="idx-btn ib-view">
                                <i class="fas fa-utensils"></i> Meals
                            </a>
                            @endcan
                            @if($sub->pause_logs()->count() > 0)
                            <a href="{{ route('admin.user-subcrptions.pause-logs', $sub->id) }}" class="idx-btn ib-purple">
                                <i class="fas fa-history"></i> Logs
                            </a>
                            @endif
                            @can('user_subcrption_delete')
                            <form action="{{ route('admin.user-subcrptions.destroy', $sub->id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
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
    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
    @can('user_subcrption_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.user-subcrptions.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt) {
            var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) { return $(entry).data('entry-id'); });
            if (!ids.length) { alert('{{ trans('global.datatables.zero_selected') }}'); return; }
            if (confirm('{{ trans('global.areYouSure') }}')) {
                $.ajax({ headers: {'x-csrf-token': _token}, method: 'POST', url: "{{ route('admin.user-subcrptions.massDestroy') }}", data: { ids: ids, _method: 'DELETE' } }).done(function () { location.reload(); });
            }
        }
    });
    @endcan
    $.extend(true, $.fn.dataTable.defaults, { orderCellsTop: true, order: [[0, 'desc']], pageLength: 25 });
    $('.datatable-UserSubcrption:not(.ajaxTable)').DataTable({
        buttons: dtButtons,
        columnDefs: [{ orderable: false, targets: -1 }],
        select: false
    });
});
</script>
@endsection
