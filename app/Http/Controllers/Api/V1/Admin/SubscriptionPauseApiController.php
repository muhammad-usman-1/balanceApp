<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubcrption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionPauseApiController extends Controller
{
    /**
     * Pause a subscription (for mobile app users)
     * 
     * @param Request $request
     * @param int $subscriptionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function pause(Request $request, $subscriptionId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'reason' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $subscription = UserSubcrption::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found or you do not have permission to pause this subscription.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($subscription->is_paused) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is already paused.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($subscription->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active subscriptions can be paused.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $subscription->pause(
            $request->days,
            $request->reason,
            'user',
            $user->id,
            $user->name,
            $request->notes
        );

        if (is_array($result) && $result['success']) {
            $subscription->refresh();
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'subscription' => [
                    'id' => $subscription->id,
                    'is_paused' => $subscription->is_paused,
                    'paused_at' => $subscription->paused_at,
                    'paused_until' => $subscription->paused_until,
                    'total_paused_days' => $subscription->total_paused_days,
                    'end_date' => $subscription->end_date,
                ],
                'pause_details' => $result['data'] ?? null,
            ], Response::HTTP_OK);
        }

        $errorMessage = is_array($result) && isset($result['message']) 
            ? $result['message'] 
            : 'Unable to pause subscription.';
        
        return response()->json([
            'success' => false,
            'message' => $errorMessage,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Resume a subscription (for mobile app users)
     * 
     * @param Request $request
     * @param int $subscriptionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function resume(Request $request, $subscriptionId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $subscription = UserSubcrption::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found or you do not have permission to resume this subscription.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$subscription->is_paused) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is not paused.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $subscription->resume(
            'user',
            $user->id,
            $user->name,
            $request->notes
        );

        if (is_array($result) && $result['success']) {
            $subscription->refresh();
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'subscription' => [
                    'id' => $subscription->id,
                    'is_paused' => $subscription->is_paused,
                    'total_paused_days' => $subscription->total_paused_days,
                    'end_date' => $subscription->end_date,
                ],
                'resume_details' => $result['data'] ?? null,
            ], Response::HTTP_OK);
        }

        $errorMessage = is_array($result) && isset($result['message']) 
            ? $result['message'] 
            : 'Unable to resume subscription.';
        
        return response()->json([
            'success' => false,
            'message' => $errorMessage,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get pause/resume logs for a subscription (for mobile app users)
     * 
     * @param int $subscriptionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function pauseLogs($subscriptionId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = UserSubcrption::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found or you do not have permission to view this subscription.',
            ], Response::HTTP_NOT_FOUND);
        }

        $pauseLogs = $subscription->pause_logs()
            ->orderBy('action_timestamp', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'action_timestamp' => $log->action_timestamp,
                    'paused_at' => $log->paused_at,
                    'resumed_at' => $log->resumed_at,
                    'paused_days' => $log->paused_days,
                    'reason' => $log->reason,
                    'performed_by_type' => $log->performed_by_type,
                    'performed_by_name' => $log->performed_by_name,
                    'notes' => $log->notes,
                    'metadata' => $log->metadata,
                ];
            });

        return response()->json([
            'success' => true,
            'subscription_id' => $subscription->id,
            'pause_logs' => $pauseLogs,
        ], Response::HTTP_OK);
    }
}
