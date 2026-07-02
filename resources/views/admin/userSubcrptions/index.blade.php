@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-clipboard-list mr-2" style="color:#16a34a;"></i> Subscriptions</h3>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success" style="margin:16px 20px 0;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="idx-flash idx-flash-error" style="margin:16px 20px 0;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Personalized</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userSubcrptions as $sub)
                    <tr>
                        <td style="font-weight:600; color:#111827;">#{{ $sub->id }}</td>
                        <td>
                            <span style="font-weight:600; color:#111827;">{{ $sub->user->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="idx-chip chip-violet">{{ $sub->subcrption_plans->title ?? '—' }}</span>
                        </td>
                        <td style="white-space:nowrap; font-size:.8rem;">{{ $sub->start_date ?? '—' }}</td>
                        <td style="white-space:nowrap; font-size:.8rem;">{{ $sub->end_date ?? '—' }}</td>
                        <td>
                            @if($sub->payment === 'paid')
                                <span class="idx-chip chip-green"><i class="fas fa-check" style="font-size:.55rem;"></i> Paid</span>
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
@can('user_subcrption_delete')
                            <form action="{{ route('admin.user-subcrptions.destroy', $sub->id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="idx-empty">
                            <i class="fas fa-clipboard-list" style="font-size:2rem; color:#d1d5db;"></i><br>
                            No subscriptions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($userSubcrptions->hasPages())
        <div style="padding: 14px 20px; border-top: 1px solid #f3f4f6;">
            {{ $userSubcrptions->links('partials.pagination') }}
        </div>
        @endif
    </div>
</div>

@endsection
