<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SubcrptionPlan;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionRenewalApiController extends Controller
{
    /**
     * GET /api/v1/my-subscriptions/{id}/renewal
     *
     * Returns renewal status for a given active subscription:
     * - whether auto-renew is on
     * - the queued renewal record (if already created by auto-renew job or manual purchase)
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $subscription = UserSubcrption::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->with(['queuedSubscription.subcrption_plans'])
            ->first();

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found.'], Response::HTTP_NOT_FOUND);
        }

        $queued = $subscription->queuedSubscription;

        return response()->json([
            'success' => true,
            'data'    => [
                'subscription_id'     => $subscription->id,
                'auto_renew'          => (bool) $subscription->auto_renew,
                'renewal_notified_at' => $subscription->renewal_notified_at,
                'renewal'             => $queued ? [
                    'id'           => $queued->id,
                    'plan'         => $queued->subcrption_plans,
                    'selected_days'=> $queued->selected_days,
                    'start_date'   => $queued->start_date,
                    'end_date'     => $queued->end_date,
                    'price'        => $queued->price,
                    'currency'     => $queued->currency,
                    'payment'      => $queued->payment,
                    'status'       => $queued->status,
                ] : null,
            ],
        ]);
    }

    /**
     * POST /api/v1/my-subscriptions/{id}/cancel-renewal
     *
     * Turns off auto-renew for the subscription and deletes any unpaid queued renewal.
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        $subscription = UserSubcrption::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->with('queuedSubscription')
            ->first();

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found.'], Response::HTTP_NOT_FOUND);
        }

        $subscription->auto_renew = false;
        $subscription->save();

        $queued = $subscription->queuedSubscription;
        if ($queued && $queued->payment === 'pending' && $queued->status === 'queued') {
            $queued->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Auto-renewal cancelled. Your plan will not renew after it ends.',
        ]);
    }

    /**
     * PUT /api/v1/my-subscriptions/{id}/renewal-plan
     *
     * Change the plan on the queued renewal subscription.
     * The app calls this when the user picks a different plan from the renewal page.
     */
    public function changePlan(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'subcrption_plans_id' => ['required', 'integer', 'exists:subcrption_plans,id'],
        ]);

        $subscription = UserSubcrption::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->with('queuedSubscription')
            ->first();

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found.'], Response::HTTP_NOT_FOUND);
        }

        $queued = $subscription->queuedSubscription;
        if (! $queued) {
            return response()->json([
                'success' => false,
                'message' => 'No pending renewal found for this subscription.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($queued->payment === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change plan — the renewal has already been paid.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newPlan = SubcrptionPlan::where('id', $request->subcrption_plans_id)
            ->where('is_active', true)
            ->first();

        if (! $newPlan) {
            return response()->json([
                'success' => false,
                'message' => 'The selected plan is not currently available.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newStart = Carbon::parse($queued->getRawOriginal('start_date'));
        $newEnd   = $newStart->copy()->addWeeks((int) $newPlan->no_of_weeks);

        $queued->subcrption_plans_id        = $newPlan->id;
        $queued->price                      = $newPlan->price;
        $queued->attributes['end_date']     = $newEnd->format('Y-m-d');
        $queued->save();

        return response()->json([
            'success' => true,
            'message' => 'Renewal plan updated successfully.',
            'data'    => [
                'renewal_id' => $queued->id,
                'plan'       => $newPlan,
                'start_date' => $queued->start_date,
                'end_date'   => $queued->end_date,
                'price'      => $queued->price,
            ],
        ]);
    }
}
