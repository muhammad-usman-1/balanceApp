<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPauseRequest;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionPauseRequestController extends Controller
{
    /**
     * Submit a pause request for an active subscription.
     *
     * POST /api/v1/subscription/{subscriptionId}/pause-request
     *
     * Body:
     *   pause_start_date  string  Y-m-d  required
     *   pause_end_date    string  Y-m-d  required, after pause_start_date
     *   reason            string         required
     */
    public function store(Request $request, $subscriptionId): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'pause_start_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'pause_end_date'   => 'required|date_format:Y-m-d|after:pause_start_date',
            'reason'           => 'required|string|max:1000',
        ]);

        $subscription = UserSubcrption::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($subscription->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active subscriptions can be paused.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($subscription->is_paused) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is already paused.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $existing = SubscriptionPauseRequest::where('user_subcrption_id', $subscriptionId)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending pause request for this subscription.',
                'data'    => $this->formatRequest($existing),
            ], Response::HTTP_BAD_REQUEST);
        }

        $start    = Carbon::parse($request->pause_start_date);
        $end      = Carbon::parse($request->pause_end_date);
        $pauseDays = (int) $start->diffInDays($end);

        $pauseRequest = SubscriptionPauseRequest::create([
            'user_subcrption_id' => $subscription->id,
            'user_id'            => $user->id,
            'pause_start_date'   => $request->pause_start_date,
            'pause_end_date'     => $request->pause_end_date,
            'pause_days'         => $pauseDays,
            'reason'             => $request->reason,
            'status'             => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pause request submitted successfully. Please wait for admin approval.',
            'data'    => $this->formatRequest($pauseRequest),
        ], Response::HTTP_OK);
    }

    /**
     * List all pause requests for a subscription (owned by the authenticated user).
     *
     * GET /api/v1/subscription/{subscriptionId}/pause-requests
     */
    public function index($subscriptionId): JsonResponse
    {
        $user = Auth::user();

        $subscription = UserSubcrption::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $requests = SubscriptionPauseRequest::where('user_subcrption_id', $subscriptionId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => $this->formatRequest($r));

        return response()->json([
            'success'         => true,
            'subscription_id' => (int) $subscriptionId,
            'data'            => $requests,
        ], Response::HTTP_OK);
    }

    private function formatRequest(SubscriptionPauseRequest $r): array
    {
        return [
            'id'               => $r->id,
            'subscription_id'  => $r->user_subcrption_id,
            'pause_start_date' => $r->pause_start_date?->format('Y-m-d'),
            'pause_end_date'   => $r->pause_end_date?->format('Y-m-d'),
            'pause_days'       => $r->pause_days,
            'reason'           => $r->reason,
            'status'           => $r->status,
            'admin_notes'      => $r->admin_notes,
            'reviewed_at'      => $r->reviewed_at?->format('Y-m-d H:i:s'),
            'created_at'       => $r->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
