<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DeliveryOrder;
use App\Models\Meal;
use App\Models\SubscriptionDay;
use App\Models\UserSubcrption;
use App\Models\User;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $today   = Carbon::today();
        $dayName = strtolower($today->format('l'));

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

        // Today's deliveries
        $todayDeliveryTotal    = DeliveryOrder::whereDate('delivery_date', $today)->count();
        $todayDelivered        = DeliveryOrder::whereDate('delivery_date', $today)->where('status', 'delivered')->count();
        $todayPending          = DeliveryOrder::whereDate('delivery_date', $today)->where('status', 'pending')->count();

        $totalCategories     = Category::count();
        $totalCoupons        = Coupon::count();
        $activeCoupons       = Coupon::where('status', 'active')->count();
        $totalAreas          = Area::count();
        $totalBranches       = Branch::count();

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
