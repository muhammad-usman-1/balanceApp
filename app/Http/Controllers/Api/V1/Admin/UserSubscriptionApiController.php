<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserSubscriptionApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $subscriptions = UserSubcrption::where('user_id', $user->id)
            ->with(['subcrption_plans', 'address', 'queuedSubscription.subcrption_plans'])
            ->latest()
            ->get();

        $active = $subscriptions->filter(function ($sub) {
            return $sub->status === 'active'
                && Carbon::parse($sub->getRawOriginal('end_date'))->isAfter(now()->startOfDay());
        })->values();

        $queued = $subscriptions->filter(function ($sub) {
            return $sub->status === 'queued';
        })->values();

        $recent = $subscriptions->reject(function ($sub) use ($active, $queued) {
            return $active->contains('id', $sub->id) || $queued->contains('id', $sub->id);
        })->take(10)->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'active' => $active,
                'queued' => $queued,
                'recent' => $recent,
            ],
        ]);
    }

    public function show($id, Request $request)
    {
        $user = $request->user();

        $subscription = UserSubcrption::where('user_id', $user->id)
            ->where('id', $id)
            ->with([
                'user',
                'subcrption_plans',
                'address',
                'branch',
                'area',
                'subscription_days.subscription_meals.meal',
                'pause_logs',
            ])
            ->first();

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $subscription,
        ]);
    }
}
