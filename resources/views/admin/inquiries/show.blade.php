@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
.content-wrapper { background: #fff !important; }
.inq-detail-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin-bottom:4px; }
.inq-detail-value { font-size:.88rem; color:#111827; }
.inq-box { background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:16px 18px; margin-bottom:0; }
.inq-reply-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:16px 18px; }
</style>

<div style="margin-bottom:16px;">
    <a href="{{ route('admin.inquiries.index') }}" class="idx-btn ib-gray">
        <i class="fas fa-arrow-left"></i> Back to Inquiries
    </a>
</div>

<div class="row">
    <div class="col-lg-8">

        {{-- Inquiry detail --}}
        <div class="card idx-card mb-4">
            <div class="card-header">
                <h3><i class="fas fa-envelope-open-text mr-2" style="color:#0d9488;"></i> {{ $inquiry->subject }}</h3>
                <div>
                    @if($inquiry->status === 'new')
                        <span class="idx-chip chip-red"><i class="fas fa-circle" style="font-size:.4rem;"></i> New</span>
                    @elseif($inquiry->status === 'read')
                        <span class="idx-chip chip-blue"><i class="fas fa-eye" style="font-size:.55rem;"></i> Read</span>
                    @else
                        <span class="idx-chip chip-green"><i class="fas fa-reply" style="font-size:.55rem;"></i> Replied</span>
                    @endif
                </div>
            </div>
            <div class="card-body" style="padding:20px 24px !important;">

                {{-- Customer info row --}}
                <div class="row mb-4">
                    <div class="col-sm-4">
                        <div class="inq-detail-label">Customer</div>
                        <div class="inq-detail-value" style="font-weight:600;">{{ $inquiry->name }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="inq-detail-label">Mobile</div>
                        <div class="inq-detail-value">{{ $inquiry->mobile ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="inq-detail-label">Email</div>
                        <div class="inq-detail-value">{{ $inquiry->email ?? '—' }}</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-4">
                        <div class="inq-detail-label">Submitted</div>
                        <div class="inq-detail-value">{{ $inquiry->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="inq-detail-label">Subject</div>
                        <div class="inq-detail-value" style="font-weight:600;">{{ $inquiry->subject }}</div>
                    </div>
                    @if($inquiry->user_id)
                    <div class="col-sm-4">
                        <div class="inq-detail-label">User ID</div>
                        <div class="inq-detail-value">#{{ $inquiry->user_id }}</div>
                    </div>
                    @endif
                </div>

                {{-- Message --}}
                <div class="inq-detail-label">Message</div>
                <div class="inq-box" style="white-space:pre-wrap; line-height:1.7; font-size:.87rem; color:#374151;">{{ $inquiry->description }}</div>

                {{-- Admin reply (if exists) --}}
                @if($inquiry->admin_reply)
                <div style="margin-top:20px;">
                    <div class="inq-detail-label">Admin Reply</div>
                    <div class="inq-reply-box">
                        <div style="white-space:pre-wrap; line-height:1.7; font-size:.87rem; color:#166534;">{{ $inquiry->admin_reply }}</div>
                        @if($inquiry->repliedBy)
                        <div style="margin-top:8px; font-size:.75rem; color:#9ca3af;">
                            Replied by <strong>{{ $inquiry->repliedBy->name }}</strong>
                            on {{ $inquiry->replied_at?->format('d M Y, h:i A') }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- Reply form --}}
        @if($inquiry->status !== 'replied')
        <div class="card idx-card">
            <div class="card-header">
                <h3><i class="fas fa-reply mr-2" style="color:#16a34a;"></i> Send Reply</h3>
            </div>
            <div class="card-body" style="padding:20px 24px !important;">
                <form action="{{ route('admin.inquiries.reply', $inquiry->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label style="font-size:.82rem; font-weight:600; color:#374151; margin-bottom:6px; display:block;">
                            Reply Message <span style="color:#ef4444;">*</span>
                        </label>
                        <textarea name="admin_reply" rows="5" required
                                  style="width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:8px;
                                         font-size:.87rem; color:#111827; outline:none; resize:vertical;"
                                  placeholder="Type your reply here…">{{ old('admin_reply') }}</textarea>
                        @error('admin_reply')
                            <div style="font-size:.75rem; color:#ef4444; margin-top:4px;">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="idx-btn ib-green" style="padding:8px 20px; font-size:.83rem;">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

    {{-- Right sidebar --}}
    <div class="col-lg-4">
        <div class="card idx-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle mr-2" style="color:#6b7280;"></i> Details</h3>
            </div>
            <div class="card-body" style="padding:16px 20px !important;">
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <div class="inq-detail-label">Status</div>
                        @if($inquiry->status === 'new')
                            <span class="idx-chip chip-red">New</span>
                        @elseif($inquiry->status === 'read')
                            <span class="idx-chip chip-blue">Read</span>
                        @else
                            <span class="idx-chip chip-green">Replied</span>
                        @endif
                    </div>
                    <div>
                        <div class="inq-detail-label">Received</div>
                        <div class="inq-detail-value">{{ $inquiry->created_at->format('d M Y') }}</div>
                        <div style="font-size:.75rem; color:#9ca3af;">{{ $inquiry->created_at->diffForHumans() }}</div>
                    </div>
                    @if($inquiry->user)
                    <div>
                        <div class="inq-detail-label">Linked User</div>
                        <div class="inq-detail-value" style="font-weight:600;">{{ $inquiry->user->name }}</div>
                        <div style="font-size:.75rem; color:#9ca3af;">ID #{{ $inquiry->user_id }}</div>
                    </div>
                    @endif
                </div>

                <hr style="border-color:#f3f4f6; margin:16px 0;">

                <form action="{{ route('admin.inquiries.destroy', $inquiry->id) }}" method="POST"
                      onsubmit="return confirm('Delete this inquiry?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="idx-btn ib-del" style="width:100%; justify-content:center;">
                        <i class="fas fa-trash"></i> Delete Inquiry
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
