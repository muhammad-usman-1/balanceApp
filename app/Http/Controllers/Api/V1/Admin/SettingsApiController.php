<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsApiController extends Controller
{
    // These are the fixed slot keys accepted by the checkout validation.
    // label_en / label_ar are for display in the app.
    private const DELIVERY_SLOTS = [
        'four_pm_to_eight_pm'  => ['label_en' => '4:00 PM – 8:00 PM',  'label_ar' => '٤:٠٠ م – ٨:٠٠ م'],
        'eight_pm_to_midnight' => ['label_en' => '8:00 PM – Midnight',  'label_ar' => '٨:٠٠ م – منتصف الليل'],
    ];

    /**
     * GET /api/v1/settings
     *
     * Returns enabled payment methods and available delivery time slots.
     * The app calls this before showing the checkout screen.
     */
    public function index(): JsonResponse
    {
        $setting = Setting::firstOrCreateDefault();

        // --- Payment methods ---
        $methods = [];

        if ($setting->payment_knet) {
            $methods[] = [
                'id'          => 'knet',
                'label'       => 'KNET',
                'description' => 'Kuwait electronic payment network',
                'type'        => 'redirect',
            ];
        }

        if ($setting->payment_credit_card) {
            $methods[] = [
                'id'          => 'credit_card',
                'label'       => 'Credit / Debit Card',
                'description' => 'Visa, Mastercard accepted',
                'type'        => 'direct',
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

        // --- Delivery time slots ---
        // Each slot has:
        //   value    → send this in checkout request as address.preferred_delivery_slot
        //   label_en → display in app (English)
        //   label_ar → display in app (Arabic)
        $slots = [];
        foreach (self::DELIVERY_SLOTS as $value => $labels) {
            $slots[] = [
                'value'    => $value,
                'label_en' => $labels['label_en'],
                'label_ar' => $labels['label_ar'],
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'payment_methods'     => array_values($methods),
                'delivery_time_slots' => $slots,
            ],
        ]);
    }
}
