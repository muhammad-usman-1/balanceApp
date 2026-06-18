<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTimeSlot;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsApiController extends Controller
{
    public function index(): JsonResponse
    {
        $setting = Setting::firstOrCreateDefault();

        // Payment methods — only return enabled ones
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
                'type'        => 'redirect',
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

        // Delivery time slots — from database, managed by admin
        $slots = DeliveryTimeSlot::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($s) => [
                'value'    => $s->value,
                'label_en' => $s->label_en,
                'label_ar' => $s->label_ar ?? '',
            ])
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data'    => [
                'payment_methods'     => array_values($methods),
                'delivery_time_slots' => $slots,
            ],
        ]);
    }
}
