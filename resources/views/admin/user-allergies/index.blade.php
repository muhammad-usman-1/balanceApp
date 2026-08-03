@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')

<style>

    .content-wrapper { background: #fff !important; }

.allergy-tag {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .72rem; font-weight: 600; padding: 3px 9px; border-radius: 20px;
    background: #f3f4f6; color: #374151; white-space: nowrap; margin: 2px 2px;
}
.search-bar {
    display: flex; gap: 8px; align-items: center;
}
.search-bar input {
    border: 1px solid #e5e7eb; border-radius: 8px; padding: 6px 12px;
    font-size: .83rem; outline: none; width: 240px;
}
.search-bar input:focus { border-color: #ef4444; box-shadow: 0 0 0 2px rgba(239,68,68,.12); }
.stat-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fee2e2; color: #dc2626; border-radius: 20px;
    font-size: .75rem; font-weight: 700; padding: 4px 12px;
}
</style>

<div class="card idx-card">
    <div class="card-header" style="flex-wrap:wrap; gap:10px;">
        <h3>
            <i class="fas fa-allergies mr-2" style="color:#dc2626;"></i>
            User Allergies
            <span class="stat-badge ml-2">
                <i class="fas fa-exclamation-circle"></i>
                {{ $users->total() }} {{ Str::plural('user', $users->total()) }}
            </span>
        </h3>
        <form method="GET" action="{{ route('admin.user-allergies.index') }}" class="search-bar">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, mobile, email…">
            <button type="submit" class="idx-btn" style="background:#fee2e2;color:#dc2626;">
                <i class="fas fa-search"></i> Search
            </button>
            @if($search)
            <a href="{{ route('admin.user-allergies.index') }}" class="idx-btn ib-gray">
                <i class="fas fa-times"></i> Clear
            </a>
            @endif
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>User</th>
                        <th>Mobile</th>
                        <th>Allergies</th>
                        <th style="width:120px;">Joined</th>
                        <th style="width:90px;">Profile</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    @php $initial = strtoupper(substr($user->name ?? 'U', 0, 1)); @endphp
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $user->id }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;font-size:.72rem;font-weight:700;flex-shrink:0;">
                                    {{ $initial }}
                                </span>
                                <div>
                                    <div style="font-weight:600;color:#111827;">{{ $user->name ?? '—' }}</div>
                                    <div style="font-size:.75rem;color:#9ca3af;">{{ $user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color:#6b7280;font-size:.82rem;">
                            {{ $user->country_code ? '+' . $user->country_code . ' ' : '' }}{{ $user->mobile ?? '—' }}
                        </td>
                        <td>
                            @forelse($user->allergies as $allergy)
                                <span class="allergy-tag">{{ $allergy }}</span>
                            @empty
                                <span style="color:#9ca3af;font-size:.8rem;">—</span>
                            @endforelse
                        </td>
                        <td style="color:#6b7280;font-size:.8rem;">
                            {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—' }}
                        </td>
                        <td>
                            @can('user_show')
                            <a href="{{ route('admin.users.show', $user->id) }}" class="idx-btn ib-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="idx-empty">
                            <i class="fas fa-check-circle" style="font-size:2rem;color:#bbf7d0;display:block;margin-bottom:8px;"></i>
                            No users with food allergies found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div style="padding:16px 20px;border-top:1px solid #f3f4f6;">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
