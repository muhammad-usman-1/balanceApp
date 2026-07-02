@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-map-marker-alt mr-2" style="color:#059669;"></i> Areas</h3>
        <a href="{{ route('admin.areas.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Area
        </a>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="idx-flash idx-flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Area Name</th>
                        <th>Delivery Charges (KWD)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($areas as $area)
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $area->id }}</td>
                        <td style="font-weight:600;">{{ $area->name }}</td>
                        <td>
                            <span style="font-weight:700;">{{ number_format($area->delivery_charges, 2) }}</span>
                            <span style="color:#9ca3af;font-size:.78rem;"> KWD</span>
                        </td>
                        <td>
                            @if($area->status === 'active')
                                <span class="idx-chip chip-green"><i class="fas fa-circle" style="font-size:.4rem;"></i> Active</span>
                            @else
                                <span class="idx-chip chip-gray">{{ ucfirst($area->status) }}</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.areas.edit', $area->id) }}" class="idx-btn ib-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <form action="{{ route('admin.areas.destroy', $area->id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="idx-empty"><i class="fas fa-map-marker-alt"></i><br>No areas found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
