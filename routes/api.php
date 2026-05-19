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

    $otpCode = (int) $credentials['otp'];
    
    // Find user by mobile
    $user = User::where('mobile', $credentials['mobile'])->first();
    
    if (! $user) {
        return response()->json(['error' => 'Invalid mobile or OTP'], 401);
    }
    
    // Check if OTP matches stored value
    $storedOtp = (int) $user->otp;
    if ($storedOtp != $otpCode) {
        return response()->json(['error' => 'Invalid mobile or OTP'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    // Get active subscription
    $activeSubscription = \App\Models\UserSubcrption::where('user_id', $user->id)
        ->where('status', 'active')
        ->where('end_date', '>=', now()->format('Y-m-d'))
        ->with(['subcrption_plans', 'address'])
        ->latest()
        ->first();

    $subscriptionData = null;
    if ($activeSubscription) {
        $plan = $activeSubscription->subcrption_plans;
        $subscriptionData = [
            'id'                    => $activeSubscription->id,
            'subscription_plan_id'  => $activeSubscription->subcrption_plans_id,
            'subscription_plan_title' => $plan->title ?? null,
            'no_of_weeks'           => $plan->no_of_weeks ?? null,
            'days_per_week'         => $plan->min_days ?? null,
            'selected_days'         => $activeSubscription->selected_days,
            'start_date'            => $activeSubscription->start_date,
            'end_date'              => $activeSubscription->end_date,
            'price'                 => $activeSubscription->price,
            'currency'              => $activeSubscription->currency,
            'payment'               => $activeSubscription->payment,
            'payment_reference'     => $activeSubscription->payment_reference,
            'card_brand'            => $activeSubscription->card_brand,
            'card_last_four'        => $activeSubscription->card_last_four,
            'status'                => $activeSubscription->status,
            'is_personalized'       => $activeSubscription->is_personalized ?? false,
            'protein'               => $activeSubscription->protein,
            'carbs'                 => $activeSubscription->carbs,
            'is_paused'             => $activeSubscription->is_paused ?? false,
            'paused_at'             => $activeSubscription->paused_at,
            'paused_until'          => $activeSubscription->paused_until,
            'total_paused_days'     => $activeSubscription->total_paused_days ?? 0,
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

// OTP Routes - Public, no authentication required
Route::post('otp/send', 'Api\V1\OtpController@sendOtp')->name('otp.send');
Route::post('otp/verify', 'Api\V1\OtpController@verifyOtp')->name('otp.verify');

// Subscription Checkout - Public, no authentication required
Route::post('v1/subscription/checkout', 'Api\V1\Admin\SubscriptionCheckoutApiController@store')->name('subscription.checkout');

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin'], function () {
    // Areas — mobile shows areas only; branch is auto-resolved from selected area
    Route::get('areas', 'AreaApiController@index')->name('areas.index');

    // Branches (admin/internal use only)
    Route::get('branches', 'BranchApiController@index')->name('branches.index');
    Route::get('branches/{branch}/areas', 'BranchApiController@areas')->name('branches.areas');

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

    // Protein Options — public read (app fetches for personalized plan pricing)
    Route::get('protein-options', 'ProteinOptionApiController@index')->name('protein-options.index');

    // Protein Options — admin CRUD
    Route::get('protein-options/all', 'ProteinOptionApiController@all')->name('protein-options.all');
    Route::post('protein-options', 'ProteinOptionApiController@store')->name('protein-options.store');
    Route::get('protein-options/{proteinOption}', 'ProteinOptionApiController@show')->name('protein-options.show');
    Route::put('protein-options/{proteinOption}', 'ProteinOptionApiController@update')->name('protein-options.update');
    Route::delete('protein-options/{proteinOption}', 'ProteinOptionApiController@destroy')->name('protein-options.destroy');

    // Payments
    Route::get('payment/kits', 'HesabePaymentController@reviewKits')->name('payment.kits');
    Route::post('payment/checkout', 'HesabePaymentController@checkout')->name('payment.checkout');

    // Durations
    Route::apiResource('durations', 'DurationsApiController');

    // Subscription Meals - Update meal for any day
    Route::post('subscription/meals/update', 'SubscriptionMealApiController@updateMeal')->name('subscription.meals.update');
    Route::get('subscription/meals', 'SubscriptionMealApiController@getMeals')->name('subscription.meals.get');

    // Coupon validation
    Route::post('coupons/validate', 'CouponApiController@validateCoupon')->name('coupons.validate');
});

// Authenticated user routes for subscription pause/resume/my-subscriptions
Route::group(['prefix' => 'v1', 'as' => 'api.', 'middleware' => ['auth:sanctum']], function () {
    // Logout - revokes the current API token
    Route::post('logout', 'Api\V1\UserRegistrationController@logout')->name('logout');
    Route::post('subscription/{subscriptionId}/pause', 'Api\V1\Admin\SubscriptionPauseApiController@pause')->name('subscription.pause');
    Route::post('subscription/{subscriptionId}/resume', 'Api\V1\Admin\SubscriptionPauseApiController@resume')->name('subscription.resume');
    Route::get('subscription/{subscriptionId}/pause-logs', 'Api\V1\Admin\SubscriptionPauseApiController@pauseLogs')->name('subscription.pause-logs');

    // User Subscriptions
    Route::get('my-subscriptions', 'Api\V1\Admin\UserSubscriptionApiController@index')->name('my-subscriptions.index');
    Route::get('my-subscriptions/{id}', 'Api\V1\Admin\UserSubscriptionApiController@show')->name('my-subscriptions.show');
});
