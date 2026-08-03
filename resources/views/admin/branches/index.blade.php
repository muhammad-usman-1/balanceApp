@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-code-branch mr-2" style="color:#0d9488;"></i> Branches</h3>
        <a href="{{ route('admin.branches.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Branch
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
                        <th>Branch Name</th>
                        <th>Associated Areas</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $branch->id }}</td>
                        <td style="font-weight:600;">
                            {{ $branch->name }}
                            @if($branch->name_ar)
                                <div style="font-size:.75rem;color:#9ca3af;font-weight:400;" dir="rtl">{{ $branch->name_ar }}</div>
                            @endif
                        </td>
                        <td>
                            @if($branch->areas->count() > 0)
                                @foreach($branch->areas as $area)
                                    <span class="idx-chip chip-teal" style="margin:1px;">{{ $area->name }}</span>
                                @endforeach
                            @else
                                <span style="color:#9ca3af;font-size:.78rem;">No areas assigned</span>
                            @endif
                        </td>
                        <td>
                            @if($branch->status === 'active')
                                <span class="idx-chip chip-green"><i class="fas fa-circle" style="font-size:.4rem;"></i> Active</span>
                            @else
                                <span class="idx-chip chip-gray">{{ ucfirst($branch->status) }}</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.branches.edit', $branch->id) }}" class="idx-btn ib-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="idx-empty"><i class="fas fa-code-branch"></i><br>No branches found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
