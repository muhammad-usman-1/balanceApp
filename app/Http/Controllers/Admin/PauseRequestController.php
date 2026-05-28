<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPauseRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PauseRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $pauseRequests = SubscriptionPauseRequest::with([
            'user:id,name,mobile,email',
            'subscription:id,subcrption_plans_id,start_date,end_date,status,is_paused',
            'subscription.subcrption_plans:id,title',
            'reviewer:id,name',
        ])
        ->when($status !== 'all', fn($q) => $q->where('status', $status))
        ->orderBy('created_at', 'desc')
        ->get();

        $counts = [
            'pending'  => SubscriptionPauseRequest::where('status', 'pending')->count(),
            'approved' => SubscriptionPauseRequest::where('status', 'approved')->count(),
            'rejected' => SubscriptionPauseRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.pauseRequests.index', compact('pauseRequests', 'status', 'counts'));
    }

    public function approve(Request $request, SubscriptionPauseRequest $pauseRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($pauseRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been reviewed.');
        }

        $subscription = $pauseRequest->subscription;

        if (!$subscription || $subscription->status !== 'active') {
            return redirect()->back()->with('error', 'Subscription is no longer active.');
        }

        if ($subscription->is_paused) {
            return redirect()->back()->with('error', 'Subscription is already paused.');
        }

        $result = $subscription->pauseByRequest(
            $pauseRequest->pause_start_date->format('Y-m-d'),
            $pauseRequest->pause_end_date->format('Y-m-d'),
            $pauseRequest->reason,
            'admin',
            auth()->id(),
            auth()->user()->name ?? 'Admin',
            $request->admin_notes
        );

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        $pauseRequest->update([
            'status'      => 'approved',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pause request approved. Subscription paused successfully.');
    }

    public function reject(Request $request, SubscriptionPauseRequest $pauseRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($pauseRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been reviewed.');
        }

        $pauseRequest->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pause request rejected.');
    }
}
