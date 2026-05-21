@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
.user-avatar {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg,#3b82f6,#2563eb);
    color: #fff; display: inline-flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; margin-right: 8px; vertical-align: middle;
}
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-users mr-2" style="color:#2563eb;"></i> Customers</h3>
        @can('user_create')
        <a href="{{ route('admin.users.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Customer
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div style="margin:16px 20px 0; padding:10px 14px; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; border-radius:8px; font-size:.83rem;">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table datatable datatable-User" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Gender</th>
                        <th>Height</th>
                        <th>Weight</th>
                        <th>DOB</th>
                        <th>Activity</th>
                        <th>Goal</th>
                        <th>Allergies</th>
                        <th>Joined</th>
                        <th>Affiliated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    @php
                        $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
                        $primaryAddress = $user->addresses->firstWhere('is_primary', true) ?? $user->addresses->first();
                    @endphp
                    <tr data-entry-id="{{ $user->id }}">
                        <td style="font-weight:600; color:#111827;">#{{ $user->id }}</td>
                        <td>
                            <span class="user-avatar">{{ $initial }}</span>
                            <span style="font-weight:600;">{{ $user->name ?? '—' }}</span>
                            @if($user->email)
                                <div style="font-size:.72rem; color:#9ca3af;">{{ $user->email }}</div>
                            @endif
                        </td>
                        <td>{{ $user->mobile ?? '—' }}</td>
                        <td>
                            @if($user->gender)
                                <span class="idx-chip {{ $user->gender === 'male' ? 'chip-blue' : 'chip-green' }}">
                                    {{ ucfirst($user->gender) }}
                                </span>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td>{{ $user->height ? $user->height.' cm' : '—' }}</td>
                        <td>{{ $user->weight ? $user->weight.' kg' : '—' }}</td>
                        <td style="white-space:nowrap;">{{ $user->dob ?? '—' }}</td>
                        <td>{{ $user->activity_level ?? '—' }}</td>
                        <td>{{ $user->goal ?? '—' }}</td>
                        <td>
                            @if($user->has_food_allergies)
                                <span class="idx-chip chip-red">Yes</span>
                                @if(!empty($user->allergies))
                                <div style="font-size:.7rem; color:#9ca3af; margin-top:2px;">{{ implode(', ', $user->allergies) }}</div>
                                @endif
                            @else
                                <span style="color:#9ca3af; font-size:.78rem;">No</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap; color:#6b7280; font-size:.78rem;">
                            {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—' }}
                        </td>
                        <td>
                            @if($user->affiliatedCode)
                                <span class="idx-chip chip-teal" title="{{ $user->affiliatedCode->full_name }}">
                                    {{ $user->affiliatedCode->code }}
                                </span>
                            @else
                                <span style="color:#9ca3af; font-size:.78rem;">None</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            @can('user_show')
                            <a href="{{ route('admin.users.show', $user->id) }}" class="idx-btn ib-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @endcan
                            @can('user_edit')
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="idx-btn ib-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            @endcan
                            @can('user_delete')
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                  onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display:inline-block;">
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
    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);

    let affiliateActive = {{ request('affiliate_users') ? 'true' : 'false' }};
    dtButtons.push({
        text: 'Affiliate Users',
        className: affiliateActive ? 'btn-info active' : 'btn-info',
        action: function () {
            var url = new URL(window.location.href);
            affiliateActive ? url.searchParams.delete('affiliate_users') : url.searchParams.set('affiliate_users', '1');
            window.location.href = url.toString();
        }
    });

    @can('user_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.users.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt) {
            var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) { return $(entry).data('entry-id'); });
            if (!ids.length) { alert('{{ trans('global.datatables.zero_selected') }}'); return; }
            if (confirm('{{ trans('global.areYouSure') }}')) {
                $.ajax({ headers: {'x-csrf-token': _token}, method: 'POST', url: "{{ route('admin.users.massDestroy') }}", data: { ids: ids, _method: 'DELETE' } }).done(function () { location.reload(); });
            }
        }
    });
    @endcan

    $.extend(true, $.fn.dataTable.defaults, { orderCellsTop: true, order: [[0, 'desc']], pageLength: 25 });
    $('.datatable-User:not(.ajaxTable)').DataTable({
        buttons: dtButtons,
        columnDefs: [{ orderable: false, targets: -1 }],
        select: false
    });
});
</script>
@endsection
