@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
.content-wrapper { background: #fff !important; }

/* Tabs */
.inq-tabs { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 16px 20px 0; }
.inq-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 20px; font-size: .8rem; font-weight: 600;
    text-decoration: none; border: 1px solid #e5e7eb; background: #fff; color: #6b7280;
    transition: border-color .15s, background .15s, color .15s;
}
.inq-tab:hover { text-decoration: none; border-color: #d1d5db; }
.inq-tab-count {
    font-size: .7rem; font-weight: 700; padding: 1px 7px; border-radius: 10px;
    background: #f3f4f6; color: #6b7280;
}
.inq-tab.is-active { color: #fff; }
.inq-tab.is-active.tab-all     { background: #4b5563; border-color: #4b5563; }
.inq-tab.is-active.tab-new     { background: #dc2626; border-color: #dc2626; }
.inq-tab.is-active.tab-read    { background: #2563eb; border-color: #2563eb; }
.inq-tab.is-active.tab-replied { background: #16a34a; border-color: #16a34a; }
.inq-tab.is-active .inq-tab-count { background: rgba(255,255,255,.25); color: #fff; }

/* Search bar */
.inq-filter-bar {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    padding: 14px 20px; margin-top: 8px;
}
.inq-search { position: relative; }
.inq-search input {
    padding: 8px 14px 8px 32px; border-radius: 9px;
    border: 1px solid #d1d5db; font-size: .84rem; height: 36px; width: 260px; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.inq-search input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.12); }
.inq-search .fa-search { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:.78rem; }

/* Table */
.inq-row-new td:first-child { box-shadow: inset 3px 0 0 #dc2626; }
.inq-row-new { background: #fffbf5; }

.inq-customer-name { font-weight: 600; color: #111827; font-size: .87rem; }
.inq-customer-email { font-size: .72rem; color: #9ca3af; }

.inq-subject { font-weight: 600; color: #111827; font-size: .85rem; }
.inq-desc { font-size: .8rem; color: #6b7280; line-height: 1.4; white-space: normal; }
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
                <span class="idx-chip chip-red ml-2">{{ $counts['new'] }} new</span>
            @endif
        </h3>
        <span class="idx-chip chip-gray">{{ $inquiries->total() }} total</span>
    </div>

    {{-- Status tabs --}}
    <div class="inq-tabs">
        @foreach(['all', 'new', 'read', 'replied'] as $tab)
        <a href="{{ route('admin.inquiries.index', array_merge(request()->query(), ['status' => $tab, 'page' => 1])) }}"
           class="inq-tab tab-{{ $tab }} {{ $status === $tab ? 'is-active' : '' }}">
            {{ ucfirst($tab) }}
            @if($tab !== 'all')
                <span class="inq-tab-count">{{ $counts[$tab] }}</span>
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
        <button type="submit" class="idx-btn ib-view" style="height:36px;">
            <i class="fas fa-search"></i> Search
        </button>
        @if($search)
        <a href="{{ route('admin.inquiries.index', ['status' => $status]) }}" class="idx-btn ib-gray" style="height:36px;">
            <i class="fas fa-times"></i> Clear
        </a>
        @endif
    </form>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th style="width:110px;">Customer</th>
                        <th style="width:110px;">Mobile</th>
                        <th style="width:110px;">Subject</th>
                        <th>Description</th>
                        <th style="width:90px;">Status</th>
                        <th style="width:95px;">Date</th>
                        <th style="width:110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inq)
                    <tr class="{{ $inq->status === 'new' ? 'inq-row-new' : '' }}">
                        <td style="font-weight:600; color:#111827;">{{ $inq->id }}</td>
                        <td>
                            <div class="inq-customer-name">{{ $inq->name }}</div>
                            @if($inq->email)
                                <div class="inq-customer-email">{{ $inq->email }}</div>
                            @endif
                        </td>
                        <td style="font-size:.82rem; color:#374151;">{{ $inq->mobile ?? '—' }}</td>
                        <td class="inq-subject">{{ $inq->subject }}</td>
                        <td class="inq-desc">{{ $inq->description }}</td>
                        <td>
                            @if($inq->status === 'new')
                                <span class="idx-chip chip-red">New</span>
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
