<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPauseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminPauseRequestController extends Controller
{
    /**
     * List pause requests. Admin only.
     *
     * GET /api/v1/admin/pause-requests?status=pending
     *
     * Query params:
     *   status  string  pending|approved|rejected  (omit for all)
     */
    public function index(Request $request): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], Response::HTTP_FORBIDDEN);
        }

        $query = SubscriptionPauseRequest::with([
            'user:id,name,mobile,email',
            'subscription:id,subcrption_plans_id,start_date,end_date,status,is_paused',
            'subscription.subcrption_plans:id,title',
        ])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $request->validate(['status' => 'in:pending,approved,rejected']);
            $query->where('status', $request->status);
        }

        $requests = $query->get()->map(fn($r) => $this->formatRequest($r));

        return response()->json([
            'success' => true,
            'data'    => $requests,
        ], Response::HTTP_OK);
    }

    /**
     * Approve a pause request and immediately apply the pause. Admin only.
     *
     * POST /api/v1/admin/pause-requests/{id}/approve
     *
     * Body:
     *   admin_notes  string  optional
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $admin = Auth::user();

        if (!$admin->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $pauseRequest = SubscriptionPauseRequest::where('id', $id)
            ->where('status', 'pending')
            ->with('subscription')
            ->first();

        if (!$pauseRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pause request not found or already reviewed.',
            ], Response::HTTP_NOT_FOUND);
        }

        $subscription = $pauseRequest->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Associated subscription no longer exists.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($subscription->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is no longer active and cannot be paused.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($subscription->is_paused) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is already paused.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Apply the pause using the user-requested dates
        $result = $subscription->pauseByRequest(
            $pauseRequest->pause_start_date->format('Y-m-d'),
            $pauseRequest->pause_end_date->format('Y-m-d'),
            $pauseRequest->reason,
            'admin',
            $admin->id,
            $admin->name,
            $request->admin_notes
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $pauseRequest->update([
            'status'      => 'approved',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $subscription->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Pause request approved. Subscription has been paused.',
            'data'    => [
                'pause_request'  => $this->formatRequest($pauseRequest->fresh()),
                'pause_details'  => $result['data'],
                'subscription'   => [
                    'id'               => $subscription->id,
                    'is_paused'        => $subscription->is_paused,
                    'paused_at'        => $subscription->paused_at?->format('Y-m-d'),
                    'paused_until'     => $subscription->paused_until?->format('Y-m-d'),
                    'total_paused_days'=> $subscription->total_paused_days,
                    'end_date'         => $subscription->attributes['end_date'],
                ],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Reject a pause request. Admin only.
     *
     * POST /api/v1/admin/pause-requests/{id}/reject
     *
     * Body:
     *   admin_notes  string  optional
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $admin = Auth::user();

        if (!$admin->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $pauseRequest = SubscriptionPauseRequest::where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$pauseRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pause request not found or already reviewed.',
            ], Response::HTTP_NOT_FOUND);
        }

        $pauseRequest->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pause request rejected.',
            'data'    => $this->formatRequest($pauseRequest->fresh()),
        ], Response::HTTP_OK);
    }

    private function formatRequest(SubscriptionPauseRequest $r): array
    {
        return [
            'id'               => $r->id,
            'subscription_id'  => $r->user_subcrption_id,
            'user'             => $r->user ? [
                'id'     => $r->user->id,
                'name'   => $r->user->name,
                'mobile' => $r->user->mobile,
                'email'  => $r->user->email,
            ] : null,
            'subscription_plan'=> $r->subscription?->subcrption_plans?->title,
            'pause_start_date' => $r->pause_start_date?->format('Y-m-d'),
            'pause_end_date'   => $r->pause_end_date?->format('Y-m-d'),
            'pause_days'       => $r->pause_days,
            'reason'           => $r->reason,
            'status'           => $r->status,
            'admin_notes'      => $r->admin_notes,
            'reviewed_by'      => $r->reviewed_by,
            'reviewed_at'      => $r->reviewed_at?->format('Y-m-d H:i:s'),
            'created_at'       => $r->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
