<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPauseRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        ->paginate(25)
        ->withQueryString();

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

        $newEndDate = Carbon::parse($subscription->getRawOriginal('end_date'))
            ->addDays($pauseRequest->pause_days)
            ->format('Y-m-d');

        DB::table('user_subcrptions')->where('id', $subscription->id)->update([
            'total_paused_days' => $subscription->total_paused_days + $pauseRequest->pause_days,
            'end_date'          => $newEndDate,
            'updated_at'        => now(),
        ]);

        $pauseRequest->update([
            'status'      => 'approved',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pause request approved. Delivery will be skipped on the requested day(s).');
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
