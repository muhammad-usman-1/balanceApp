<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DeliveryOrder;
use App\Models\Meal;
use App\Models\SubscriptionPauseRequest;
use App\Models\UserSubcrption;
use App\Models\User;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $user  = auth()->user();

        if ($user->isBranchUser()) {
            return $this->branchDashboard($today, $user->branch_id);
        }

        return $this->adminDashboard($today);
    }

    private function branchDashboard(Carbon $today, int $branchId)
    {
        $branchDeliveryTotal = DeliveryOrder::whereDate('delivery_date', $today)
            ->where('branch_id', $branchId)->count();

        $branchDelivered = DeliveryOrder::whereDate('delivery_date', $today)
            ->where('branch_id', $branchId)->where('status', 'delivered')->count();

        $branchPending = DeliveryOrder::whereDate('delivery_date', $today)
            ->where('branch_id', $branchId)->where('status', 'pending')->count();

        $branchActiveSubscriptions = UserSubcrption::where('branch_id', $branchId)
            ->where('status', 'active')
            ->where('is_paused', false)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $branchTotalSubscriptions = UserSubcrption::where('branch_id', $branchId)->count();

        $branchTodaySubscriptions = UserSubcrption::where('branch_id', $branchId)
            ->whereDate('created_at', $today)->count();

        $branchPauseRequests = SubscriptionPauseRequest::where('status', 'pending')->count();

        $branchTotalMeals = Meal::count();

        $recentDeliveries = DeliveryOrder::with(['subscription.user'])
            ->whereDate('delivery_date', $today)
            ->where('branch_id', $branchId)
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact(
            'branchDeliveryTotal',
            'branchDelivered',
            'branchPending',
            'branchActiveSubscriptions',
            'branchTotalSubscriptions',
            'branchTodaySubscriptions',
            'branchPauseRequests',
            'branchTotalMeals',
            'recentDeliveries'
        ));
    }

    private function adminDashboard(Carbon $today)
    {
        $totalUsers          = User::count();
        $totalMeals          = Meal::count();
        $totalSubscriptions  = UserSubcrption::count();
        $activeSubscriptions = UserSubcrption::where('status', 'active')
            ->where('is_paused', false)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $monthlySubscriptions = UserSubcrption::whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->count();

        $todaySubscriptions = UserSubcrption::whereDate('created_at', $today)->count();
        $todayMeals         = Meal::whereDate('created_at', $today)->count();

        $todayDeliveryTotal = DeliveryOrder::whereDate('delivery_date', $today)->count();
        $todayDelivered     = DeliveryOrder::whereDate('delivery_date', $today)->where('status', 'delivered')->count();
        $todayPending       = DeliveryOrder::whereDate('delivery_date', $today)->where('status', 'pending')->count();

        $totalCategories = Category::count();
        $totalCoupons    = Coupon::count();
        $activeCoupons   = Coupon::where('status', 'active')->count();
        $totalAreas      = Area::count();
        $totalBranches   = Branch::count();

        $recentSubscriptions = UserSubcrption::with(['user', 'subcrption_plans'])
            ->latest()->take(6)->get();

        return view('home', compact(
            'totalUsers',
            'totalMeals',
            'totalSubscriptions',
            'activeSubscriptions',
            'monthlySubscriptions',
            'todaySubscriptions',
            'todayMeals',
            'todayDeliveryTotal',
            'todayDelivered',
            'todayPending',
            'totalCategories',
            'totalCoupons',
            'activeCoupons',
            'totalAreas',
            'totalBranches',
            'recentSubscriptions'
        ));
    }
}
