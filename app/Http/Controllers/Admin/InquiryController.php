<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppInquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = trim($request->get('search', ''));

        $inquiries = AppInquiry::with('user:id,name,mobile')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', '%'.$search.'%')
                   ->orWhere('subject', 'like', '%'.$search.'%')
                   ->orWhere('mobile', 'like', '%'.$search.'%');
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'new'     => AppInquiry::where('status', 'new')->count(),
            'read'    => AppInquiry::where('status', 'read')->count(),
            'replied' => AppInquiry::where('status', 'replied')->count(),
        ];

        return view('admin.inquiries.index', compact('inquiries', 'status', 'search', 'counts'));
    }

    public function show(AppInquiry $inquiry)
    {
        if ($inquiry->status === 'new') {
            $inquiry->update(['status' => 'read']);
        }

        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function reply(Request $request, AppInquiry $inquiry)
    {
        $request->validate(['admin_reply' => 'required|string|max:5000']);

        $inquiry->update([
            'admin_reply' => $request->admin_reply,
            'status'      => 'replied',
            'replied_by'  => auth()->id(),
            'replied_at'  => now(),
        ]);

        return redirect()->route('admin.inquiries.index')
            ->with('success', 'Reply sent successfully.');
    }

    public function destroy(AppInquiry $inquiry)
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')
            ->with('success', 'Inquiry deleted.');
    }
}
