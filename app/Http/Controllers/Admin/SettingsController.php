<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTimeSlot;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->user() || ! auth()->user()->is_admin) {
                abort(403, 'Unauthorized. Only administrators can access settings.');
            }
            return $next($request);
        });
    }

    public function edit()
    {
        $setting = Setting::firstOrCreateDefault();
        $slots   = DeliveryTimeSlot::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.settings.edit', compact('setting', 'slots'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'payment_knet'        => 'nullable|boolean',
            'payment_credit_card' => 'nullable|boolean',
            'payment_cash'        => 'nullable|boolean',
        ]);

        $setting = Setting::firstOrCreateDefault();
        $setting->update([
            'payment_knet'        => $request->has('payment_knet'),
            'payment_credit_card' => $request->has('payment_credit_card'),
            'payment_cash'        => $request->has('payment_cash'),
        ]);

        return redirect()->route('admin.settings.edit')->with('success', 'Settings saved successfully.');
    }

    public function storeSlot(Request $request)
    {
        $request->validate([
            'label_en' => 'required|string|max:100',
            'label_ar' => 'nullable|string|max:100',
        ]);

        $value = DeliveryTimeSlot::generateValue($request->label_en);

        $maxOrder = DeliveryTimeSlot::max('sort_order') ?? 0;

        DeliveryTimeSlot::create([
            'value'      => $value,
            'label_en'   => trim($request->label_en),
            'label_ar'   => trim($request->label_ar ?? ''),
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.settings.edit')->with('success', 'Time slot added.');
    }

    public function destroySlot(DeliveryTimeSlot $slot)
    {
        $slot->delete();
        return redirect()->route('admin.settings.edit')->with('success', 'Time slot deleted.');
    }
}
