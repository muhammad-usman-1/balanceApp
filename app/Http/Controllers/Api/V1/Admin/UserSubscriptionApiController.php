<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
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

    /**
     * Per-day delivery status for the subscription's active calendar days.
     *
     * Only weekdays that are actually part of this subscription's plan
     * (subscription_days) are returned — e.g. a plan with max_days = 5
     * only marks 5 days per week, never the full 7.
     *
     * Status per day:
     *  - 'preparing' : today, and the delivery order hasn't been marked delivered yet
     *  - 'delivered'  : today (already marked delivered) OR any past scheduled day
     *  - 'upcoming'   : future scheduled day
     *  - 'paused'     : falls inside an approved/active pause window
     *
     * GET /api/v1/my-subscriptions/{id}/calendar?month=YYYY-MM
     */
    public function calendar($id, Request $request)
    {
        $user = $request->user();

        $subscription = UserSubcrption::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['subscription_days', 'pause_requests'])
            ->first();

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $today       = Carbon::today();
        $rangeStart  = Carbon::parse($subscription->getRawOriginal('start_date'))->startOfDay();
        $rangeEnd    = Carbon::parse($subscription->getRawOriginal('end_date'))->startOfDay();

        $month = $request->get('month');
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $monthStart = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
            $monthEnd   = $monthStart->copy()->endOfMonth();

            if ($monthEnd->lt($rangeStart) || $monthStart->gt($rangeEnd)) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'subscription_id' => $subscription->id,
                        'start_date'      => $rangeStart->toDateString(),
                        'end_date'        => $rangeEnd->toDateString(),
                        'calendar'        => [],
                    ],
                ]);
            }

            $rangeStart = $monthStart->gt($rangeStart) ? $monthStart : $rangeStart;
            $rangeEnd   = $monthEnd->lt($rangeEnd) ? $monthEnd : $rangeEnd;
        }

        // Only the weekdays actually assigned to this subscription (limited by plan max_days)
        $activeDayNames = $subscription->subscription_days->pluck('day')->unique()->values()->all();

        // Collect paused date ranges (approved pause requests + a currently-active admin pause)
        $pausedRanges = $subscription->pause_requests
            ->where('status', 'approved')
            ->map(fn ($p) => [Carbon::parse($p->pause_start_date), Carbon::parse($p->pause_end_date)])
            ->values();

        if ($subscription->is_paused && $subscription->paused_at && $subscription->paused_until) {
            $pausedRanges->push([
                Carbon::parse($subscription->paused_at)->startOfDay(),
                Carbon::parse($subscription->paused_until)->startOfDay(),
            ]);
        }

        $deliveryOrders = DeliveryOrder::where('user_subcrption_id', $subscription->id)
            ->whereBetween('delivery_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->get()
            ->keyBy(fn ($o) => $o->delivery_date->toDateString());

        $calendar = [];
        $cursor   = $rangeStart->copy();

        while ($cursor->lte($rangeEnd)) {
            $dayName = strtolower($cursor->format('l'));

            if (in_array($dayName, $activeDayNames, true)) {
                $dateStr  = $cursor->toDateString();
                $isPaused = $pausedRanges->contains(
                    fn ($range) => $cursor->gte($range[0]) && $cursor->lte($range[1])
                );

                if ($isPaused) {
                    $status = 'paused';
                } elseif ($cursor->lt($today)) {
                    $status = 'delivered';
                } elseif ($cursor->isSameDay($today)) {
                    $order  = $deliveryOrders->get($dateStr);
                    $status = ($order && $order->status === 'delivered') ? 'delivered' : 'preparing';
                } else {
                    $status = 'upcoming';
                }

                $calendar[] = [
                    'date'   => $dateStr,
                    'day'    => $dayName,
                    'status' => $status,
                ];
            }

            $cursor->addDay();
        }

        $todayEntry = collect($calendar)->firstWhere('date', $today->toDateString());

        return response()->json([
            'success' => true,
            'data'    => [
                'subscription_id' => $subscription->id,
                'start_date'      => $rangeStart->toDateString(),
                'end_date'        => $rangeEnd->toDateString(),
                'active_days'     => $activeDayNames,
                'today_status'    => $todayEntry['status'] ?? null,
                'calendar'        => $calendar,
            ],
        ]);
    }
}
