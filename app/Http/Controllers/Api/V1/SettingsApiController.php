<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsApiController extends Controller
{
    /**
     * GET /v1/settings
     *
     * Returns the app-relevant settings: enabled payment methods and
     * delivery time slots. The mobile app calls this on startup to
     * know which payment options to display at checkout.
     */
    public function index(): JsonResponse
    {
        $setting = Setting::firstOrCreateDefault();

        $methods = [];

        if ($setting->payment_knet) {
            $methods[] = [
                'id'          => 'knet',
                'label'       => 'KNET',
                'description' => 'Kuwait electronic payment network',
                'type'        => 'redirect', // app opens a WebView URL
            ];
        }

        if ($setting->payment_credit_card) {
            $methods[] = [
                'id'          => 'credit_card',
                'label'       => 'Credit / Debit Card',
                'description' => 'Visa, Mastercard, etc.',
                'type'        => 'direct', // app collects card details inline
            ];
        }

        if ($setting->payment_cash) {
            $methods[] = [
                'id'          => 'cash',
                'label'       => 'Cash on Delivery',
                'description' => 'Pay when your order arrives',
                'type'        => 'offline',
            ];
        }

        $timeSlots = array_filter([
            $setting->delivery_time_slot_1_en ?: null,
            $setting->delivery_time_slot_2_en ?: null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'payment_methods'    => array_values($methods),
                'delivery_time_slots' => array_values($timeSlots),
            ],
        ]);
    }
}
