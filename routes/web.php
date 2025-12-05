<?php

Route::redirect('/', '/login');
Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

Auth::routes(['register' => false]);

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
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

    // User Subcrption
    Route::delete('user-subcrptions/destroy', 'UserSubcrptionController@massDestroy')->name('user-subcrptions.massDestroy');
    Route::get('user-subcrptions/{userSubcrption}/details', 'UserSubcrptionController@details')->name('user-subcrptions.details');
    Route::resource('user-subcrptions', 'UserSubcrptionController');

    // Subscription Plan Days (now shows subscription_days data)
    Route::delete('subscription-plan-days/destroy', 'SubscriptionPlanDaysController@massDestroy')->name('subscription-plan-days.massDestroy');
    Route::resource('subscription-plan-days', 'SubscriptionPlanDaysController');

    // Subscription Meals
    Route::delete('subscription-meals/destroy', 'SubscriptionMealsController@massDestroy')->name('subscription-meals.massDestroy');
    Route::resource('subscription-meals', 'SubscriptionMealsController');

    // Categories
    Route::post('categories/{id}/restore', 'CategoryController@restore')->name('categories.restore');
    Route::delete('categories/{id}/force-delete', 'CategoryController@forceDelete')->name('categories.force-delete');
    Route::resource('categories', 'CategoryController');
});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});
