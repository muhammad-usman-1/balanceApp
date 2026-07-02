@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
</style>
{{-- ═══════════════════════════════════════════════════════
     FULL ADMIN ACCOUNTS  (superadmin only)
═══════════════════════════════════════════════════════ --}}
@if(auth()->user()->is_admin)
<div class="card idx-card" style="margin-bottom:28px;">
    <div class="card-header">
        <h3><i class="fas fa-user-shield mr-2" style="color:#7c3aed;"></i> Full Admin Accounts</h3>
        @can('user_create')
        <a href="{{ route('admin.users.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Admin
        </a>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th style="width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fullAdmins as $admin)
                    @php $initial = strtoupper(substr($admin->name ?? 'A', 0, 1)); @endphp
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $admin->id }}</td>
                        <td>
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;font-size:.72rem;font-weight:700;margin-right:8px;vertical-align:middle;">{{ $initial }}</span>
                            <span style="font-weight:600;">{{ $admin->name ?? '—' }}</span>
                        </td>
                        <td style="color:#6b7280;font-size:.82rem;">{{ $admin->email ?? '—' }}</td>
                        <td>
                            @foreach($admin->roles as $role)
                                <span class="idx-chip" style="background:#ede9fe;color:#6d28d9;">{{ $role->title }}</span>
                            @endforeach
                        </td>
                        <td style="white-space:nowrap;">
                            @can('user_edit')
                            <a href="{{ route('admin.users.edit', $admin->id) }}" class="idx-btn ib-edit"><i class="fas fa-pen"></i> Edit</a>
                            @endcan
                            @can('user_delete')
                            <form action="{{ route('admin.users.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:32px;">No admin accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     BRANCH ADMIN ACCOUNTS
═══════════════════════════════════════════════════════ --}}
<div class="card idx-card" style="margin-bottom:28px;">
    <div class="card-header">
        <h3><i class="fas fa-code-branch mr-2" style="color:#0891b2;"></i> Branch Admin Accounts</h3>
        @can('user_create')
        <a href="{{ route('admin.users.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Branch Admin
        </a>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Branch</th>
                        <th style="width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchAdmins as $admin)
                    @php $initial = strtoupper(substr($admin->name ?? 'B', 0, 1)); @endphp
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $admin->id }}</td>
                        <td>
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#0891b2,#0e7490);color:#fff;font-size:.72rem;font-weight:700;margin-right:8px;vertical-align:middle;">{{ $initial }}</span>
                            <span style="font-weight:600;">{{ $admin->name ?? '—' }}</span>
                        </td>
                        <td style="color:#6b7280;font-size:.82rem;">{{ $admin->email ?? '—' }}</td>
                        <td>
                            @if($admin->branch)
                                <span class="idx-chip" style="background:#cffafe;color:#0e7490;">
                                    <i class="fas fa-map-marker-alt" style="font-size:.65rem;margin-right:3px;"></i>{{ $admin->branch->name }}
                                </span>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            @can('user_edit')
                            <a href="{{ route('admin.users.edit', $admin->id) }}" class="idx-btn ib-edit"><i class="fas fa-pen"></i> Edit</a>
                            @endcan
                            @can('user_delete')
                            <form action="{{ route('admin.users.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:32px;">No branch admin accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     APP CUSTOMERS
═══════════════════════════════════════════════════════ --}}
<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-users mr-2" style="color:#2563eb;"></i> App Customers</h3>
        @can('user_create')
        <a href="{{ route('admin.users.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Customer
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
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
                    @foreach($customers as $user)
                    @php
                        $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
                    @endphp
                    <tr data-entry-id="{{ $user->id }}">
                        <td style="font-weight:600;color:#111827;">#{{ $user->id }}</td>
                        <td>
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-size:.72rem;font-weight:700;margin-right:8px;vertical-align:middle;flex-shrink:0;">{{ $initial }}</span>
                            <span style="font-weight:600;">{{ $user->name ?? '—' }}</span>
                            @if($user->email)
                                <div style="font-size:.72rem;color:#9ca3af;">{{ $user->email }}</div>
                            @endif
                        </td>
                        <td>{{ $user->mobile ?? '—' }}</td>
                        <td>
                            @if($user->gender)
                                <span class="idx-chip {{ $user->gender === 'male' ? 'chip-blue' : 'chip-green' }}">{{ ucfirst($user->gender) }}</span>
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
                                    <div style="font-size:.7rem;color:#9ca3af;margin-top:2px;">{{ implode(', ', $user->allergies) }}</div>
                                @endif
                            @else
                                <span style="color:#9ca3af;font-size:.78rem;">No</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;color:#6b7280;font-size:.78rem;">
                            {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—' }}
                        </td>
                        <td>
                            @if($user->affiliatedCode)
                                <span class="idx-chip chip-teal" title="{{ $user->affiliatedCode->full_name }}">{{ $user->affiliatedCode->code }}</span>
                            @else
                                <span style="color:#9ca3af;font-size:.78rem;">None</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            @can('user_show')
                            <a href="{{ route('admin.users.show', $user->id) }}" class="idx-btn ib-view"><i class="fas fa-eye"></i> View</a>
                            @endcan
                            @can('user_edit')
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="idx-btn ib-edit"><i class="fas fa-pen"></i></a>
                            @endcan
                            @can('user_delete')
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display:inline-block;">
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

        @if($customers->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #f3f4f6;">
            {{ $customers->links('partials.pagination') }}
        </div>
        @endif
    </div>
</div>

@endsection
