@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-link mr-2" style="color:#0284c7;"></i> Affiliated Codes</h3>
        <a href="{{ route('admin.affiliated-codes.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Code
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
                        <th>Full Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Usage Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($affiliatedCodes as $code)
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $code->id }}</td>
                        <td style="font-weight:600;">{{ $code->full_name }}</td>
                        <td>
                            <span style="font-weight:700;font-family:monospace;font-size:.88rem;letter-spacing:.06em;color:#0284c7;">
                                {{ $code->code }}
                            </span>
                        </td>
                        <td>
                            @if($code->is_active)
                                <span class="idx-chip chip-green"><i class="fas fa-circle" style="font-size:.4rem;"></i> Active</span>
                            @else
                                <span class="idx-chip chip-gray">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @php $cnt = $code->users_count ?? 0; @endphp
                            @if($cnt > 0)
                                <span class="idx-chip chip-cyan">{{ $cnt }} user{{ $cnt != 1 ? 's' : '' }}</span>
                            @else
                                <span style="color:#9ca3af;font-size:.78rem;">0 uses</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.affiliated-codes.show', $code->id) }}" class="idx-btn ib-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('admin.affiliated-codes.edit', $code->id) }}" class="idx-btn ib-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <a href="{{ route('admin.affiliated-codes.logs', $code->id) }}" class="idx-btn ib-gray">
                                <i class="fas fa-list"></i> Logs
                            </a>
                            <form action="{{ route('admin.affiliated-codes.destroy', $code->id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this affiliated code?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="idx-empty"><i class="fas fa-link"></i><br>No affiliated codes found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
