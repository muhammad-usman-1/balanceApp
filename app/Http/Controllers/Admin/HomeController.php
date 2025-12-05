<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Meal;
use App\Models\UserSubcrption;
use App\Models\Category;
use App\Models\SubscriptionMeal;
use Carbon\Carbon;

class HomeController
{
    public function index()
    {
        // Get statistics
        $totalUsers = User::count();
        $totalMeals = Meal::count();
        $totalSubscriptions = UserSubcrption::count();
        $activeSubscriptions = UserSubcrption::where('status', 'active')
            ->where('end_date', '>=', now()->format('Y-m-d'))
            ->count();
        $totalCategories = Category::count();
        $totalMealAssignments = SubscriptionMeal::count();
        
        // Recent subscriptions
        $recentSubscriptions = UserSubcrption::with(['user', 'subcrption_plans'])
            ->latest()
            ->take(5)
            ->get();
        
        // Today's statistics
        $todaySubscriptions = UserSubcrption::whereDate('created_at', today())->count();
        $todayMeals = Meal::whereDate('created_at', today())->count();
        
        // Monthly statistics
        $monthlySubscriptions = UserSubcrption::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Payment statistics
        $paidSubscriptions = UserSubcrption::where('payment', 'paid')->count();
        $pendingSubscriptions = UserSubcrption::where('payment', 'pending')->count();

        return view('home', compact(
            'totalUsers',
            'totalMeals',
            'totalSubscriptions',
            'activeSubscriptions',
            'totalCategories',
            'totalMealAssignments',
            'recentSubscriptions',
            'todaySubscriptions',
            'todayMeals',
            'monthlySubscriptions',
            'paidSubscriptions',
            'pendingSubscriptions'
        ));
    }
}
