<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\Admin\UserResource;
use App\Services\OtpService;
use App\Models\User;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP to phone number
     *
     * @param SendOtpRequest $request
     * @return JsonResponse
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        try {
            $countryCode = $request->country_code;
            $phoneNumber = $request->phone_number;

            $result = $this->otpService->sendOtp($countryCode, $phoneNumber);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully.',
                'data' => [
                    'country_code' => $result['country_code'],
                    'phone_number' => $result['phone_number'],
                    'expires_at' => $result['expires_at'],
                    'is_sms_sent' => true,
                ],
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verify OTP code
     *
     * @param VerifyOtpRequest $request
     * @return JsonResponse
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $countryCode = $request->country_code;
            $phoneNumber = $request->phone_number;
            $otpCode = $request->otp_code;

            $result = $this->otpService->verifyOtp($countryCode, $phoneNumber, $otpCode);

            if ($result['success']) {
                $user = $result['user'];
                $responseCountryCode = $result['country_code'] ?? $countryCode;
                $responsePhoneNumber = $result['phone_number'] ?? '+' . preg_replace('/[^0-9]/', '', $countryCode . $phoneNumber);

                $userExists = $this->hasUserProfileData($user);

                if (! $userExists) {
                    // Load basic relations if user exists
                    $user->load(['roles', 'addresses']);
                    $token = $user->createToken('api-token')->plainTextToken;

                    return response()->json([
                        'success' => true,
                        'message' => 'OTP verified. User profile incomplete.',
                        'user_exists' => false,
                        'data' => [
                            'token' => $token,
                            'country_code' => $responseCountryCode,
                            'phone_number' => $responsePhoneNumber,
                            'user' => new UserResource($user),
                            'addresses' => $user->addresses,
                        ],
                    ], Response::HTTP_OK);
                }

                // If user exists (profile complete), fetch details

                // Load addresses
                $user->load('addresses');

                // Get active subscription
                $activeSubscription = UserSubcrption::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->whereDate('end_date', '>=', Carbon::today())
                    ->with([
                        'subcrption_plans', 
                        'duration', 
                        'subscription_days.subscription_meals.meal'
                    ])
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
                        'subscription_days' => $activeSubscription->subscription_days,
                    ];
                }

                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully.',
                    'user_exists' => true,
                    'data' => [
                        'token' => $token,
                        'country_code' => $responseCountryCode,
                        'phone_number' => $responsePhoneNumber,
                        'user' => new UserResource($user->load('roles')),
                        'addresses' => $user->addresses,
                        'subscription' => $subscriptionData,
                    ],
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], Response::HTTP_BAD_REQUEST);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying OTP: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Determine if a user already has profile data populated.
     */
    private function hasUserProfileData(User $user): bool
    {
        $requiredFields = [
            'name',
            'email',
            'dob',
            'gender',
            'height',
            'weight',
            'goal',
            'activity_level',
        ];

        foreach ($requiredFields as $field) {
            if (empty($user->{$field})) {
                return false;
            }
        }

        if (is_null($user->has_food_allergies)) {
            return false;
        }

        if ($user->has_food_allergies && empty($user->allergies)) {
            return false;
        }

        return true;
    }
}
