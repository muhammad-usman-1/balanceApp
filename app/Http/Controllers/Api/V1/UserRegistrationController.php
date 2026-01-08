<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckUserExistsRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\AffiliatedCode;
use App\Models\User;
use App\Models\UserSubcrption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class UserRegistrationController extends Controller
{
    /**
     * Register a new user
     *
     * This endpoint creates a new user without requiring password or authentication.
     * It accepts phone number, OTP, email, name, date of birth, gender, height,
     * weight, goal, activity level, and food allergy preferences.
     *
     * @param RegisterUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterUserRequest $request)
    {
        DB::beginTransaction();

        try {
            $mobile = (int) $request->phone_number;
            $otpProvided = (int) $request->otp;

            $user = User::where('mobile', $mobile)->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this phone number. Please request OTP first.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Verify OTP
            $storedOtp = (int) $user->otp;
            $isOtpValid = ($otpProvided === $storedOtp);

            if (! $isOtpValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP code.',
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($user->otp_expires_at && Carbon::parse($user->otp_expires_at)->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $hasFoodAllergies = $request->boolean('has_food_allergies');
            $allergies = $hasFoodAllergies ? array_values($request->input('allergies', [])) : null;

            // Handle affiliated code if provided
            $affiliatedCodeId = null;
            if ($request->has('affiliated_code') && !empty($request->affiliated_code)) {
                $affiliatedCode = AffiliatedCode::where('code', strtoupper($request->affiliated_code))
                    ->where('is_active', true)
                    ->first();
                
                if ($affiliatedCode) {
                    $affiliatedCodeId = $affiliatedCode->id;
                    // Increment usage count
                    $affiliatedCode->incrementUsage();
                }
            }

            // Prevent email collision with another user
            $emailInUse = User::where('email', $request->email)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailInUse) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered.',
                    'errors' => [
                        'email' => ['This email is already registered.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Update existing user profile instead of creating a new record
            $user->update([
                'otp' => $otpProvided,
                'email' => $request->email,
                'name' => $request->name,
                'dob' => $request->date_of_birth,
                'gender' => $request->gender,
                'height' => $request->height,
                'weight' => $request->weight,
                'goal' => $request->goal,
                'activity_level' => $request->activity_level,
                'has_food_allergies' => $hasFoodAllergies,
                'allergies' => $allergies,
                'affiliated_code_id' => $affiliatedCodeId,
            ]);

            // Load relationships for response
            $user->load(['roles', 'affiliatedCode']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => new UserResource($user),
            ], Response::HTTP_OK);

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
                        'is_personalized' => $activeSubscription->is_personalized ?? false,
                        'protein' => $activeSubscription->protein,
                        'carbs' => $activeSubscription->carbs,
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

