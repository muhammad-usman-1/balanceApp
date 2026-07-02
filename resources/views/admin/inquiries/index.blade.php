@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
.content-wrapper { background: #fff !important; }
.inq-filter-bar {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    padding: 12px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
}
.inq-search { position: relative; }
.inq-search input {
    padding: 6px 12px 6px 30px; border-radius: 20px;
    border: 1px solid #e5e7eb; font-size: .82rem; height: 32px; width: 220px; outline: none;
}
.inq-search input:focus { border-color: #111827; box-shadow: 0 0 0 2px rgba(17,24,39,.08); }
.inq-search .fa-search { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:.72rem; }
.unread-dot { width:8px; height:8px; border-radius:50%; background:#ef4444; display:inline-block; margin-right:4px; }
</style>

@if(session('success'))
    <div class="idx-flash idx-flash-success" style="margin-bottom:16px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="card idx-card">
    <div class="card-header">
        <h3>
            <i class="fas fa-envelope-open-text mr-2" style="color:#0d9488;"></i>
            App Inquiries
            @if($counts['new'] > 0)
                <span class="badge badge-danger ml-1" style="font-size:.72rem;">{{ $counts['new'] }} new</span>
            @endif
        </h3>
        <span class="badge badge-secondary" style="font-size:.75rem;">{{ $inquiries->total() }} total</span>
    </div>

    {{-- Status tabs --}}
    <div style="padding:14px 20px 10px; display:flex; gap:8px; flex-wrap:wrap;">
        @foreach(['all' => ['#4b5563','#f3f4f6'], 'new' => ['#dc2626','#fee2e2'], 'read' => ['#2563eb','#dbeafe'], 'replied' => ['#16a34a','#dcfce7']] as $tab => $colors)
        <a href="{{ route('admin.inquiries.index', array_merge(request()->query(), ['status' => $tab, 'page' => 1])) }}"
           style="padding:5px 14px; border-radius:20px; font-size:.78rem; font-weight:600; text-decoration:none;
                  background:{{ $status === $tab ? $colors[1] : '#f9fafb' }};
                  color:{{ $status === $tab ? $colors[0] : '#6b7280' }};
                  border:1px solid {{ $status === $tab ? $colors[0].'33' : '#e5e7eb' }};">
            {{ ucfirst($tab) }}
            @if($tab !== 'all')
                <span style="margin-left:4px; background:{{ $colors[0] }}22; color:{{ $colors[0] }}; border-radius:10px; padding:1px 7px; font-size:.72rem;">
                    {{ $counts[$tab] }}
                </span>
            @endif
        </a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.inquiries.index') }}" class="inq-filter-bar">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="inq-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, subject, mobile…">
        </div>
        <button type="submit" class="idx-btn ib-view" style="height:32px;">
            <i class="fas fa-search"></i> Search
        </button>
        @if($search)
        <a href="{{ route('admin.inquiries.index', ['status' => $status]) }}" class="idx-btn ib-gray" style="height:32px;">
            <i class="fas fa-times"></i> Clear
        </a>
        @endif
    </form>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Customer</th>
                        <th style="width:110px;">Mobile</th>
                        <th>Subject</th>
                        <th style="width:280px;">Description</th>
                        <th style="width:90px;">Status</th>
                        <th style="width:95px;">Date</th>
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inq)
                    <tr style="{{ $inq->status === 'new' ? 'background:#fffbeb;' : '' }}">
                        <td style="font-weight:600; color:#111827;">
                            @if($inq->status === 'new')
                                <span class="unread-dot"></span>
                            @endif
                            {{ $inq->id }}
                        </td>
                        <td>
                            <div style="font-weight:600; color:#111827;">{{ $inq->name }}</div>
                            @if($inq->email)
                                <div style="font-size:.72rem; color:#9ca3af;">{{ $inq->email }}</div>
                            @endif
                        </td>
                        <td style="font-size:.82rem; color:#374151;">{{ $inq->mobile ?? '—' }}</td>
                        <td style="font-weight:600; color:#111827;">{{ $inq->subject }}</td>
                        <td style="font-size:.8rem; color:#6b7280;">{{ Str::limit($inq->description, 100) }}</td>
                        <td>
                            @if($inq->status === 'new')
                                <span class="idx-chip chip-red"><i class="fas fa-circle" style="font-size:.4rem;"></i> New</span>
                            @elseif($inq->status === 'read')
                                <span class="idx-chip chip-blue"><i class="fas fa-eye" style="font-size:.55rem;"></i> Read</span>
                            @else
                                <span class="idx-chip chip-green"><i class="fas fa-reply" style="font-size:.55rem;"></i> Replied</span>
                            @endif
                        </td>
                        <td style="font-size:.78rem; color:#6b7280; white-space:nowrap;">
                            {{ $inq->created_at->format('d M Y') }}
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.inquiries.show', $inq->id) }}" class="idx-btn ib-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <form action="{{ route('admin.inquiries.destroy', $inq->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this inquiry?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="idx-empty">
                            <i class="fas fa-envelope-open-text" style="font-size:1.6rem;color:#e5e7eb;display:block;margin-bottom:8px;"></i>
                            No {{ $status !== 'all' ? $status : '' }} inquiries found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inquiries->hasPages())
        <div style="padding:14px 20px; border-top:1px solid #f3f4f6;">
            {{ $inquiries->links('partials.pagination') }}
        </div>
        @endif
    </div>
</div>

@endsection
