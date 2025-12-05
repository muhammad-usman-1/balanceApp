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
                'message' => 'OTP stored and marked as sent successfully.',
                'data' => [
                    'country_code' => $result['country_code'],
                    'phone_number' => $result['phone_number'],
                    'otp_code' => $result['otp_code'], // Include OTP for debugging
                    'expires_at' => $result['expires_at'],
                    'is_sms_sent' => true,
                    'sms_note' => 'Twilio SMS disabled - using canned OTP for QA.',
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

                $hasUserData = $this->hasUserProfileData($user);

                if (! $hasUserData) {
                    return response()->json([
                        'success' => true,
                        'message' => 'OTP verified. User data not found, please complete registration.',
                        'data' => [
                            'country_code' => $responseCountryCode,
                            'phone_number' => $responsePhoneNumber,
                            'verified' => true,
                            'user_data_exists' => false,
                            'user' => null,
                            'active_subscription' => null,
                        ],
                    ], Response::HTTP_OK);
                }

                // Get active subscription
                $activeSubscription = UserSubcrption::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->whereDate('end_date', '>=', Carbon::today())
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
                    'message' => $result['message'],
                    'data' => [
                            'country_code' => $responseCountryCode,
                            'phone_number' => $responsePhoneNumber,
                        'verified' => true,
                        'user_data_exists' => true,
                        'user' => new UserResource($user->load('roles')),
                        'active_subscription' => $subscriptionData,
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
