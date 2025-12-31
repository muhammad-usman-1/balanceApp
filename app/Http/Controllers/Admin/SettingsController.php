<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Check if user is admin
            if (!auth()->user() || !auth()->user()->is_admin) {
                abort(403, 'Unauthorized. Only administrators can access settings.');
            }
            return $next($request);
        });
    }

    public function edit()
    {
        $setting = Setting::firstOrCreateDefault();
        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'delivery_time_slot_1_en' => 'required|string|max:255',
            'delivery_time_slot_2_en' => 'required|string|max:255',
            'payment_knet' => 'nullable|boolean',
            'payment_credit_card' => 'nullable|boolean',
            'payment_cash' => 'nullable|boolean',
        ]);

        $setting = Setting::firstOrCreateDefault();
        
        $setting->update([
            'delivery_time_slot_1_en' => $request->delivery_time_slot_1_en,
            'delivery_time_slot_2_en' => $request->delivery_time_slot_2_en,
            'payment_knet' => $request->has('payment_knet'),
            'payment_credit_card' => $request->has('payment_credit_card'),
            'payment_cash' => $request->has('payment_cash'),
        ]);

        return redirect()->route('admin.settings.edit')->with('success', 'Settings updated successfully');
    }
}

