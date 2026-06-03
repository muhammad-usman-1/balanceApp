@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-layer-group mr-2" style="color:#4338ca;"></i> Subscription Plans</h3>
        @can('subcrption_plan_create')
        <a href="{{ route('admin.subcrption-plans.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Plan
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
                        <th>Title</th>
                        <th>Description</th>
                        <th>Price (KWD)</th>
                        <th>Weeks</th>
                        <th>Meals</th>
                        <th>Snacks</th>
                        <th>Active</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subcrptionPlans as $plan)
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $plan->id }}</td>
                        <td style="font-weight:600;">{{ $plan->title ?? '—' }}</td>
                        <td style="color:#6b7280;">{{ Str::limit($plan->description ?? '', 60) }}</td>
                        <td>
                            @if($plan->price)
                                <span style="font-weight:700;">{{ number_format($plan->price, 3) }}</span>
                            @else —
                            @endif
                        </td>
                        <td>{{ $plan->no_of_weeks ?? '—' }}</td>
                        <td>
                            @if($plan->meal_count)
                                <span class="idx-chip chip-blue">{{ $plan->meal_count }}</span>
                            @else —
                            @endif
                        </td>
                        <td>
                            @if($plan->snack_count)
                                <span class="idx-chip chip-yellow">{{ $plan->snack_count }}</span>
                            @else —
                            @endif
                        </td>
                        <td>
                            @if($plan->is_active)
                                <span class="idx-chip chip-green"><i class="fas fa-circle" style="font-size:.4rem;"></i> Active</span>
                            @else
                                <span class="idx-chip chip-gray">Inactive</span>
                            @endif
                        </td>
                        <td style="color:#6b7280;font-size:.78rem;white-space:nowrap;">
                            {{ $plan->created_at ? \Carbon\Carbon::parse($plan->created_at)->format('d M Y') : '—' }}
                        </td>
                        <td style="white-space:nowrap;">
                            @can('subcrption_plan_show')
                            <a href="{{ route('admin.subcrption-plans.show', $plan->id) }}" class="idx-btn ib-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @endcan
                            @can('subcrption_plan_edit')
                            <a href="{{ route('admin.subcrption-plans.edit', $plan->id) }}" class="idx-btn ib-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            @endcan
                            @can('subcrption_plan_delete')
                            <form action="{{ route('admin.subcrption-plans.destroy', $plan->id) }}" method="POST"
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

        @if($subcrptionPlans->hasPages())
        <div style="padding: 14px 20px; border-top: 1px solid #f3f4f6;">
            {{ $subcrptionPlans->links('partials.pagination') }}
        </div>
        @endif
    </div>
</div>

@endsection
