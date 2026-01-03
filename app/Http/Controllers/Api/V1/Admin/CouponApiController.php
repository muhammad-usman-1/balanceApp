<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CouponApiController extends Controller
{
    /**
     * Verify and validate a coupon code
     * Checks all conditions in a single endpoint:
     * - Whether the coupon exists
     * - Whether the coupon is valid or expired
     * - Whether the coupon is active or inactive
     * - Whether the usage limit has been reached
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $couponCode = strtoupper(trim($request->coupon_code));
        $userId = $request->user_id ?? (Auth::check() ? Auth::id() : null);

        // Check 1: Whether the coupon exists (including soft-deleted)
        $coupon = Coupon::withTrashed()->where('coupon_code', $couponCode)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code. The coupon does not exist.',
                'error_code' => 'COUPON_NOT_FOUND',
                'data' => null,
            ], Response::HTTP_NOT_FOUND);
        }

        // Check 2: Whether the coupon is soft-deleted
        if ($coupon->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon has been deleted and is no longer available.',
                'error_code' => 'COUPON_DELETED',
                'data' => [
                    'coupon_code' => $coupon->coupon_code,
                    'deleted_at' => $coupon->deleted_at->format('Y-m-d H:i:s'),
                ],
            ], Response::HTTP_GONE);
        }

        // Check 3: Whether the coupon is active or inactive
        if ($coupon->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This coupon is currently inactive.',
                'error_code' => 'COUPON_INACTIVE',
                'data' => [
                    'coupon_id' => $coupon->id,
                    'coupon_code' => $coupon->coupon_code,
                    'status' => $coupon->status,
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check 4: Whether the coupon is expired or not yet valid
        $now = now();
        $today = $now->format('Y-m-d');
        $startDate = $coupon->start_date->format('Y-m-d');
        $endDate = $coupon->end_date->format('Y-m-d');

        if ($today < $startDate) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon is not yet valid. It will be available from ' . $coupon->start_date->format('Y-m-d') . '.',
                'error_code' => 'COUPON_NOT_YET_VALID',
                'data' => [
                    'coupon_id' => $coupon->id,
                    'coupon_code' => $coupon->coupon_code,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'current_date' => $today,
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($today > $endDate) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon has expired. It was valid until ' . $coupon->end_date->format('Y-m-d') . '.',
                'error_code' => 'COUPON_EXPIRED',
                'data' => [
                    'coupon_id' => $coupon->id,
                    'coupon_code' => $coupon->coupon_code,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'current_date' => $today,
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check 5: Whether the usage limit has been reached (if user_id is provided)
        if ($userId) {
            $usageCount = $coupon->getUserUsageCount($userId);
            
            if ($coupon->usage_limit_per_user !== null && $usageCount >= $coupon->usage_limit_per_user) {
                return response()->json([
                    'success' => false,
                    'message' => "You have reached the maximum usage limit ({$coupon->usage_limit_per_user}) for this coupon.",
                    'error_code' => 'USAGE_LIMIT_REACHED',
                    'data' => [
                        'coupon_id' => $coupon->id,
                        'coupon_code' => $coupon->coupon_code,
                        'usage_limit_per_user' => $coupon->usage_limit_per_user,
                        'current_usage_count' => $usageCount,
                        'user_id' => $userId,
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // All validations passed - coupon is valid
        $responseData = [
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->coupon_code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $coupon->status,
            'usage_limit_per_user' => $coupon->usage_limit_per_user,
        ];

        // Add usage information if user_id is provided
        if ($userId) {
            $usageCount = $coupon->getUserUsageCount($userId);
            $responseData['user_usage_count'] = $usageCount;
            $responseData['remaining_uses'] = $coupon->usage_limit_per_user !== null 
                ? ($coupon->usage_limit_per_user - $usageCount) 
                : null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon is valid and can be used.',
            'data' => $responseData,
        ], Response::HTTP_OK);
    }
}
