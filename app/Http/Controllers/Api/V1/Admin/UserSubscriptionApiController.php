<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserSubscriptionApiController extends Controller
{
    /**
     * Get all subscriptions for the authenticated user, grouped by active and past.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $subscriptions = UserSubcrption::where('user_id', $user->id)
            ->with(['subcrption_plans', 'duration'])
            ->latest()
            ->get();

        $active = $subscriptions->filter(function ($sub) {
            return $sub->status === 'active' && 
                   Carbon::parse($sub->getRawOriginal('end_date'))->isAfter(now()->startOfDay());
        })->values();

        $recent = $subscriptions->reject(function ($sub) use ($active) {
            return $active->contains('id', $sub->id);
        })->take(10)->values();

        return response()->json([
            'success' => true,
            'data' => [
                'active' => $active,
                'recent' => $recent,
            ]
        ]);
    }

    /**
     * Get full details for a specific subscription.
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $user = $request->user();

        $subscription = UserSubcrption::where('user_id', $user->id)
            ->where('id', $id)
            ->with([
                'user',
                'subcrption_plans',
                'duration',
                'address',
                'subscription_days.subscription_meals.meal'
            ])
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $subscription
        ]);
    }
}
