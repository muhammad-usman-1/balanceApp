<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPauseRequest;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'pause_end_date'   => 'required|date_format:Y-m-d|after_or_equal:pause_start_date',
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

        // Block only if the exact same date range is already pending or approved
        $existing = SubscriptionPauseRequest::where('user_subcrption_id', $subscriptionId)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('pause_start_date', $request->pause_start_date)
            ->whereDate('pause_end_date', $request->pause_end_date)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'A pause request for this date already exists (status: ' . $existing->status . ').',
                'data'    => $this->formatRequest($existing),
            ], Response::HTTP_BAD_REQUEST);
        }

        $start     = Carbon::parse($request->pause_start_date);
        $end       = Carbon::parse($request->pause_end_date);
        // Inclusive: pause_start_date and pause_end_date both count as paused days
        $pauseDays = $start->isSameDay($end) ? 1 : (int) $start->diffInDays($end);

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

    /**
     * Cancel a pending pause request (user can cancel before admin approves).
     *
     * DELETE /api/v1/subscription/{subscriptionId}/pause-request/{pauseRequestId}
     */
    public function cancel($subscriptionId, $pauseRequestId): JsonResponse
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

        $pauseRequest = SubscriptionPauseRequest::where('id', $pauseRequestId)
            ->where('user_subcrption_id', $subscriptionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$pauseRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pause request not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($pauseRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be cancelled. This request is already ' . $pauseRequest->status . '.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $pauseRequest->update([
            'status'       => 'cancelled',
            'admin_notes'  => 'Cancelled by user.',
            'reviewed_by'  => $user->id,
            'reviewed_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pause request cancelled successfully.',
            'data'    => $this->formatRequest($pauseRequest->fresh()),
        ], Response::HTTP_OK);
    }

    /**
     * Resume (cancel) an already-approved pause request.
     * If the pause day hasn't arrived yet the end_date extension is reverted.
     *
     * POST /api/v1/subscription/{subscriptionId}/pause-request/{pauseRequestId}/resume
     */
    public function resumeRequest($subscriptionId, $pauseRequestId): JsonResponse
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

        $pauseRequest = SubscriptionPauseRequest::where('id', $pauseRequestId)
            ->where('user_subcrption_id', $subscriptionId)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();

        if (!$pauseRequest) {
            return response()->json([
                'success' => false,
                'message' => 'No approved pause request found for this subscription.',
            ], Response::HTTP_NOT_FOUND);
        }

        $pauseStart = Carbon::parse($pauseRequest->pause_start_date);

        // Revert the end_date extension only if the pause day hasn't arrived yet.
        // If the day already passed the delivery was already skipped — keep the extension.
        if ($pauseStart->isFuture()) {
            DB::table('user_subcrptions')->where('id', $subscription->id)->update([
                'end_date'          => Carbon::parse($subscription->getRawOriginal('end_date'))
                                            ->subDays($pauseRequest->pause_days)
                                            ->format('Y-m-d'),
                'total_paused_days' => max(0, $subscription->total_paused_days - $pauseRequest->pause_days),
                'updated_at'        => now(),
            ]);
        }

        $pauseRequest->update([
            'status'      => 'resumed',
            'admin_notes' => 'Resumed by user on ' . now()->format('Y-m-d H:i'),
            'reviewed_at' => now(),
        ]);

        $subscription->refresh();

        return response()->json([
            'success' => true,
            'message' => $pauseStart->isFuture()
                ? 'Pause cancelled. Your delivery will proceed as normal and your end date has been restored.'
                : 'Pause marked as resumed. The missed delivery day has been credited to your subscription.',
            'data'    => [
                'pause_request' => $this->formatRequest($pauseRequest->fresh()),
                'subscription'  => [
                    'id'                => $subscription->id,
                    'end_date'          => $subscription->getRawOriginal('end_date'),
                    'total_paused_days' => $subscription->total_paused_days,
                ],
            ],
        ], Response::HTTP_OK);
    }

    private function formatRequest(SubscriptionPauseRequest $r): array
    {
        return [
            'id'               => $r->id,
            'subscription_id'  => $r->user_subcrption_id,
            'pause_start_date' => $r->pause_start_date ? Carbon::parse($r->pause_start_date)->format('Y-m-d') : null,
            'pause_end_date'   => $r->pause_end_date   ? Carbon::parse($r->pause_end_date)->format('Y-m-d')   : null,
            'pause_days'       => $r->pause_days,
            'reason'           => $r->reason,
            'status'           => $r->status,
            'admin_notes'      => $r->admin_notes,
            'reviewed_at'      => $r->reviewed_at?->format('Y-m-d H:i:s'),
            'created_at'       => $r->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
