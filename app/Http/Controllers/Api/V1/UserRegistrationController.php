<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckUserExistsRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use App\Models\UserSubcrption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UserRegistrationController extends Controller
{
    /**
     * Register a new user
     * 
     * This endpoint creates a new user without requiring password or authentication.
     * It accepts phone number, OTP, email, name, date of birth, gender, height, and weight.
     *
     * @param RegisterUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterUserRequest $request)
    {
        DB::beginTransaction();

        try {
            // Prepare user data
            // Convert phone_number string to integer to match database schema
            $userData = [
                'mobile' => (int) $request->phone_number,
                'otp' => $request->otp,
                'email' => $request->email,
                'name' => $request->name,
                'dob' => $request->date_of_birth,
                'gender' => $request->gender,
                'height' => $request->height,
                'weight' => $request->weight,
                // No password required - user can login with mobile + OTP
            ];

            // Create user
            $user = User::create($userData);

            // Load relationships for response
            $user->load('roles');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => new UserResource($user),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('User registration error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register user',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check if a user exists in the system
     * 
     * This endpoint checks whether a user exists with the provided phone number and OTP.
     * It does not require authentication and returns a simple boolean response.
     *
     * @param CheckUserExistsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUserExists(CheckUserExistsRequest $request)
    {
        try {
            // Convert phone_number string to integer to match database schema
            $mobile = (int) $request->phone_number;
            $otp = $request->otp;

            // Check if user exists with matching phone number and OTP
            $user = User::where('mobile', $mobile)
                ->where('otp', $otp)
                ->first();

            if ($user) {
                // Get active subscription
                $activeSubscription = UserSubcrption::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('end_date', '>=', now()->format('Y-m-d'))
                    ->with(['subcrption_plans', 'duration'])
                    ->latest()
                    ->first();

                $subscriptionData = null;
                if ($activeSubscription) {
                    $subscriptionData = [
                        'id' => $activeSubscription->id,
                        'subscription_plan_id' => $activeSubscription->subcrption_plans_id,
                        'subscription_plan_title' => $activeSubscription->subcrption_plans->title ?? null,
                        'duration_id' => $activeSubscription->duration_id,
                        'duration_title' => $activeSubscription->duration->title ?? null,
                        'selected_days' => $activeSubscription->selected_days,
                        'start_date' => $activeSubscription->start_date,
                        'end_date' => $activeSubscription->end_date,
                        'price' => $activeSubscription->price,
                        'payment' => $activeSubscription->payment,
                        'status' => $activeSubscription->status,
                    ];
                }

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'message' => 'User exists in the system',
                    'data' => [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'active_subscription' => $subscriptionData,
                    ],
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'message' => 'User does not exist in the system',
                    'data' => null,
                ], Response::HTTP_OK);
            }

        } catch (\Exception $e) {
            Log::error('User existence check error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Failed to check user existence',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

