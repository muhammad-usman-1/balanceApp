<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionCheckoutRequest;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionCheckoutApiController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService)
    {
    }

    /**
     * Create user subscription and store data in all 3 tables:
     * 1. user_subcrptions - Main subscription record
     * 2. subscription_days - Days for this user subscription (store selected days separately)
     * 3. subscription_meals - Meals for each day (linked to subscription_days_id)
     *
     * @param StoreSubscriptionCheckoutRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreSubscriptionCheckoutRequest $request)
    {
        try {
            $result = $this->subscriptionService->createSubscription($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully',
                'data' => [
                    'user_subscription' => $result['user_subscription'],
                    'subscription_days' => $result['subscription_days'],
                    'subscription_meals' => $result['subscription_meals'],
                ],
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Subscription checkout error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

