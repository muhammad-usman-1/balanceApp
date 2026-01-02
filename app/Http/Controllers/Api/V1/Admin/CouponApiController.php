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
     * Validate a coupon code and check if user can use it
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

        $coupon = Coupon::where('coupon_code', strtoupper($request->coupon_code))->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code.',
            ], Response::HTTP_NOT_FOUND);
        }

        // If user_id is provided, check usage limit
        $userId = $request->user_id ?? (Auth::check() ? Auth::id() : null);
        
        if ($userId) {
            $validation = $coupon->canUserUse($userId);
            
            if (!$validation['can_use']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message'],
                    'coupon' => [
                        'id' => $coupon->id,
                        'code' => $coupon->coupon_code,
                        'type' => $coupon->type,
                        'value' => $coupon->value,
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            return response()->json([
                'success' => true,
                'message' => $validation['message'],
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->coupon_code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'usage_limit_per_user' => $coupon->usage_limit_per_user,
                    'remaining_uses' => $validation['remaining_uses'] ?? null,
                ],
            ], Response::HTTP_OK);
        }

        // If no user_id, just validate coupon exists and is active
        if ($coupon->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This coupon is not active.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $now = now()->format('Y-m-d');
        if ($now < $coupon->start_date->format('Y-m-d') || $now > $coupon->end_date->format('Y-m-d')) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon is not valid for the current date.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon is valid.',
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->coupon_code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'usage_limit_per_user' => $coupon->usage_limit_per_user,
            ],
        ], Response::HTTP_OK);
    }
}
