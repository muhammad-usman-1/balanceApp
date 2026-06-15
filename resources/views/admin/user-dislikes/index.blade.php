@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')

<style>
.dislike-tag {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .72rem; font-weight: 600; padding: 3px 9px; border-radius: 20px;
    background: #ffedd5; color: #c2410c; white-space: nowrap; margin: 2px 2px;
}
.search-bar {
    display: flex; gap: 8px; align-items: center;
}
.search-bar input {
    border: 1px solid #e5e7eb; border-radius: 8px; padding: 6px 12px;
    font-size: .83rem; outline: none; width: 240px;
}
.search-bar input:focus { border-color: #f97316; box-shadow: 0 0 0 2px rgba(249,115,22,.12); }
.stat-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #ffedd5; color: #c2410c; border-radius: 20px;
    font-size: .75rem; font-weight: 700; padding: 4px 12px;
}
</style>

<div class="card idx-card">
    <div class="card-header" style="flex-wrap:wrap; gap:10px;">
        <h3>
            <i class="fas fa-thumbs-down mr-2" style="color:#c2410c;"></i>
            User Dislikes
            <span class="stat-badge ml-2">
                <i class="fas fa-user"></i>
                {{ $users->total() }} {{ Str::plural('user', $users->total()) }}
            </span>
        </h3>
        <form method="GET" action="{{ route('admin.user-dislikes.index') }}" class="search-bar">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, mobile, email…">
            <button type="submit" class="idx-btn" style="background:#ffedd5;color:#c2410c;">
                <i class="fas fa-search"></i> Search
            </button>
            @if($search)
            <a href="{{ route('admin.user-dislikes.index') }}" class="idx-btn ib-gray">
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
                        <th>Dislikes</th>
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
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#f97316,#c2410c);color:#fff;font-size:.72rem;font-weight:700;flex-shrink:0;">
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
                            @forelse($user->dislikes as $dislike)
                                <span class="dislike-tag"><i class="fas fa-times-circle" style="font-size:.6rem;"></i> {{ $dislike }}</span>
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
                            <i class="fas fa-smile" style="font-size:2rem;color:#fed7aa;display:block;margin-bottom:8px;"></i>
                            No users with food dislikes found.
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
