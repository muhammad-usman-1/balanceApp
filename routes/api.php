<?php

use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

// API Login route for token generation using mobile + OTP
Route::post('login', function (Request $request) {
    $credentials = $request->validate([
        'mobile' => ['required', 'integer'],
        'otp' => ['required', 'integer'],
    ]);

    $user = User::where('mobile', $credentials['mobile'])
        ->where('otp', $credentials['otp'])
        ->first();

    if (! $user) {
        return response()->json(['error' => 'Invalid mobile or OTP'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    // Get active subscription
    $activeSubscription = \App\Models\UserSubcrption::where('user_id', $user->id)
        ->where('status', 'active')
        ->where('end_date', '>=', now()->format('Y-m-d'))
        ->with(['subcrption_plans', 'duration'])
        ->latest()
        ->first();

    $subscriptionData = null;
    if ($activeSubscription) {
        $subscriptionData = [
            'id' => $activeSubscription->id,
            'subscription_plan_id' => $activeSubscription->subcrption_plans_id,
            'subscription_plan_title' => $activeSubscription->subcrption_plans->title ?? null,
            'duration_id' => $activeSubscription->duration_id,
            'duration_title' => $activeSubscription->duration->title ?? null,
            'selected_days' => $activeSubscription->selected_days,
            'start_date' => $activeSubscription->start_date,
            'end_date' => $activeSubscription->end_date,
            'price' => $activeSubscription->price,
            'payment' => $activeSubscription->payment,
            'status' => $activeSubscription->status,
        ];
    }

    return response()->json([
        'token' => $token,
        'user' => new UserResource($user->load('roles')),
        'active_subscription' => $subscriptionData,
    ]);
});

// Public User Registration - No authentication required
Route::post('register', 'Api\V1\UserRegistrationController@register')->name('register');

// Check if user exists - No authentication required
Route::post('check-user', 'Api\V1\UserRegistrationController@checkUserExists')->name('check-user');

// Subscription Checkout - Public, no authentication required
Route::post('v1/subscription/checkout', 'Api\V1\Admin\SubscriptionCheckoutApiController@store')->name('subscription.checkout');

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin'], function () {
    // Category CRUD
    Route::apiResource('categories', 'CategoryApiController');
    // Get all categories for meal creation dropdown
    Route::get('meals/categories', 'MealApiController@categories')->name('meals.categories');
    // User
    Route::get('users', 'UserApiController@index')->name('users.index');
    Route::get('users/{user}', 'UserApiController@show')->name('users.show');
    Route::get('users/mobile/{mobile}', 'UserApiController@findByMobile')->name('users.findByMobile');
    // Meal
    Route::post('meals/media', 'MealApiController@storeMedia')->name('meals.storeMedia');
    Route::apiResource('meals', 'MealApiController');

    // Subcrption Plans
    Route::apiResource('subcrption-plans', 'SubcrptionPlansApiController');

    // Durations
    Route::apiResource('durations', 'DurationsApiController');
});
