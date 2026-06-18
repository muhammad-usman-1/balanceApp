<?php

Route::redirect('/', '/login');
Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

Auth::routes(['register' => false]);

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth', 'branch.scope']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Meal
    Route::delete('meals/destroy', 'MealController@massDestroy')->name('meals.massDestroy');
    Route::post('meals/media', 'MealController@storeMedia')->name('meals.storeMedia');
    Route::post('meals/ckmedia', 'MealController@storeCKEditorImages')->name('meals.storeCKEditorImages');
    Route::resource('meals', 'MealController');

    // Subcrption Plans
    Route::delete('subcrption-plans/destroy', 'SubcrptionPlansController@massDestroy')->name('subcrption-plans.massDestroy');
    Route::resource('subcrption-plans', 'SubcrptionPlansController');

    // Durations
    Route::delete('durations/destroy', 'DurationsController@massDestroy')->name('durations.massDestroy');
    Route::resource('durations', 'DurationsController');

    // Pause Requests (from app users)
    Route::get('pause-requests', 'PauseRequestController@index')->name('pause-requests.index');
    Route::post('pause-requests/{pauseRequest}/approve', 'PauseRequestController@approve')->name('pause-requests.approve');
    Route::post('pause-requests/{pauseRequest}/reject', 'PauseRequestController@reject')->name('pause-requests.reject');

    // User Subcrption
    Route::delete('user-subcrptions/destroy', 'UserSubcrptionController@massDestroy')->name('user-subcrptions.massDestroy');
    Route::get('user-subcrptions/{userSubcrption}/details', 'UserSubcrptionController@details')->name('user-subcrptions.details');
    Route::post('user-subcrptions/{userSubcrption}/pause', 'UserSubcrptionController@pause')->name('user-subcrptions.pause');
    Route::post('user-subcrptions/{userSubcrption}/resume', 'UserSubcrptionController@resume')->name('user-subcrptions.resume');
    Route::get('user-subcrptions/{userSubcrption}/pause-logs', 'UserSubcrptionController@pauseLogs')->name('user-subcrptions.pause-logs');
    Route::resource('user-subcrptions', 'UserSubcrptionController');

    // Subscription Plan Days (now shows subscription_days data)
    Route::delete('subscription-plan-days/destroy', 'SubscriptionPlanDaysController@massDestroy')->name('subscription-plan-days.massDestroy');
    Route::resource('subscription-plan-days', 'SubscriptionPlanDaysController');

    // Subscription Meals
    Route::delete('subscription-meals/destroy', 'SubscriptionMealsController@massDestroy')->name('subscription-meals.massDestroy');
    Route::resource('subscription-meals', 'SubscriptionMealsController');

    // Meal Restrictions
    Route::resource('meal-restrictions', 'MealRestrictionController')->only(['index', 'store', 'update', 'destroy']);

    // Categories
    Route::post('categories/{id}/restore', 'CategoryController@restore')->name('categories.restore');
    Route::delete('categories/{id}/force-delete', 'CategoryController@forceDelete')->name('categories.force-delete');
    Route::resource('categories', 'CategoryController');

    // Areas
    Route::resource('areas', 'AreaController');

    // Branches
    Route::resource('branches', 'BranchController');

    // Coupons
    Route::get('coupons/{coupon}/usage-history', 'CouponController@usageHistory')->name('coupons.usage-history');
    Route::resource('coupons', 'CouponController');

    // Protein Options
    Route::resource('protein-options', 'ProteinOptionController');

    // Settings (only edit/update, admin only)
    Route::get('settings', 'SettingsController@edit')->name('settings.edit');
    Route::put('settings', 'SettingsController@update')->name('settings.update');
    // Delivery time slots CRUD (managed from settings page)
    Route::post('settings/slots', 'SettingsController@storeSlot')->name('settings.slots.store');
    Route::put('settings/slots/{slot}', 'SettingsController@updateSlot')->name('settings.slots.update');
    Route::delete('settings/slots/{slot}', 'SettingsController@destroySlot')->name('settings.slots.destroy');

    // Notifications
    Route::resource('notifications', 'NotificationController');

    // Affiliated Codes
    Route::post('affiliated-codes/generate-code', 'AffiliatedCodeController@generateCode')->name('affiliated-codes.generate-code');
    Route::get('affiliated-codes/{affiliatedCode}/logs', 'AffiliatedCodeController@logs')->name('affiliated-codes.logs');
    Route::resource('affiliated-codes', 'AffiliatedCodeController');

    // User Dietary Preferences
    Route::get('user-allergies', 'UserAllergiesController@index')->name('user-allergies.index');
    Route::get('user-dislikes', 'UserDislikesController@index')->name('user-dislikes.index');

    // Delivery Orders
    Route::get('delivery-orders', 'DeliveryController@index')->name('delivery-orders.index');
    Route::get('delivery-orders/print-all', 'DeliveryController@printAll')->name('delivery-orders.print-all');
    Route::get('delivery-orders/{deliveryOrder}/print', 'DeliveryController@printNote')->name('delivery-orders.print');
    Route::post('delivery-orders/{deliveryOrder}/status', 'DeliveryController@updateStatus')->name('delivery-orders.update-status');
    Route::post('delivery-orders/make-all-delivered', 'DeliveryController@makeAllDelivered')->name('delivery-orders.make-all-delivered');
});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth', 'branch.scope']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});
