<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::withTrashed()->withCount('usages')->orderBy('created_at', 'desc')->get();
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:255|unique:coupons,coupon_code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
            'usage_limit_per_user' => 'nullable|integer|min:1',
        ]);

        // Additional validation for percentage type
        if ($request->type === 'percentage' && $request->value > 100) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['value' => 'Percentage value cannot exceed 100.']);
        }

        Coupon::create($request->only([
            'coupon_code',
            'type',
            'value',
            'start_date',
            'end_date',
            'status',
            'usage_limit_per_user'
        ]));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:255|unique:coupons,coupon_code,' . $coupon->id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
            'usage_limit_per_user' => 'nullable|integer|min:1',
        ]);

        // Additional validation for percentage type
        if ($request->type === 'percentage' && $request->value > 100) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['value' => 'Percentage value cannot exceed 100.']);
        }

        $coupon->update($request->only([
            'coupon_code',
            'type',
            'value',
            'start_date',
            'end_date',
            'status',
            'usage_limit_per_user'
        ]));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully');
    }

    /**
     * Show coupon usage history
     * 
     * @param Coupon $coupon
     * @return \Illuminate\View\View
     */
    public function usageHistory(Coupon $coupon)
    {
        $coupon->load(['usages.user']);
        $usages = $coupon->usages()->with('user')->orderBy('created_at', 'desc')->get();
        
        // Group by user to show usage count per user
        $userUsageCounts = $coupon->usages()
            ->selectRaw('user_id, COUNT(*) as usage_count')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        return view('admin.coupons.usage-history', compact('coupon', 'usages', 'userUsageCounts'));
    }
}

