v<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <span class="brand-text font-weight-light">{{ trans('panel.site_title') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}"
                       href="{{ route('admin.home') }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>{{ trans('global.dashboard') }}</p>
                    </a>
                </li>

                <!-- User Management -->
                @can('user_management_access')
                <li class="nav-item has-treeview
                    {{ request()->is('admin/permissions*') ? 'menu-open' : '' }}
                    {{ request()->is('admin/roles*') ? 'menu-open' : '' }}
                    {{ request()->is('admin/users*') ? 'menu-open' : '' }}">

                    <a class="nav-link nav-dropdown-toggle
                        {{ request()->is('admin/permissions*') ? 'active' : '' }}
                        {{ request()->is('admin/roles*') ? 'active' : '' }}
                        {{ request()->is('admin/users*') ? 'active' : '' }}" href="#">

                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            {{ trans('cruds.userManagement.title') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        @can('permission_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.index') }}"
                               class="nav-link {{ request()->is('admin/permissions*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-unlock-alt"></i>
                                <p>{{ trans('cruds.permission.title') }}</p>
                            </a>
                        </li>
                        @endcan

                        @can('role_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}"
                               class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-briefcase"></i>
                                <p>{{ trans('cruds.role.title') }}</p>
                            </a>
                        </li>
                        @endcan

                        @can('user_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}"
                               class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user"></i>
                                <p>{{ trans('cruds.user.title') }}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                <!-- Meals -->
           @can('meal_access')
<li class="nav-item">
    <a href="{{ route('admin.meals.index') }}"
       class="nav-link {{ request()->is('admin/meals*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-utensils"></i>
        <p>{{ trans('cruds.meal.title') }}</p>
    </a>
</li>
@endcan


                <!-- Categories -->
                <li class="nav-item">
                    <a href="{{ route('admin.categories.index') }}"
                       class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list-alt"></i>
                        <p>Categories</p>
                    </a>
                </li>

                <!-- Subscription Plans -->
                @can('subcrption_plan_access')
                <li class="nav-item">
                    <a href="{{ route('admin.subcrption-plans.index') }}"
                       class="nav-link {{ request()->is('admin/subcrption-plans*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-th-large"></i>
                        <p>{{ trans('cruds.subcrptionPlan.title') }}</p>
                    </a>
                </li>
                @endcan

                <!-- Durations -->
                @can('duration_access')
                <li class="nav-item">
                    <a href="{{ route('admin.durations.index') }}"
                       class="nav-link {{ request()->is('admin/durations*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clock"></i>
                        <p>{{ trans('cruds.duration.title') }}</p>
                    </a>
                </li>
                @endcan

                <!-- User Subscription -->
                @can('user_subcrption_access')
                <li class="nav-item">
                    <a href="{{ route('admin.user-subcrptions.index') }}"
                       class="nav-link {{ request()->is('admin/user-subcrptions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p>{{ trans('cruds.userSubcrption.title') }}</p>
                    </a>
                </li>
                @endcan

                <!-- Subscription Day -->
                @can('subscription_plan_day_access')
                <li class="nav-item">
                    <a href="{{ route('admin.subscription-plan-days.index') }}"
                       class="nav-link {{ request()->is('admin/subscription-plan-days*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-day"></i>
                        <p>{{ trans('cruds.subscriptionPlanDay.title') }}</p>
                    </a>
                </li>
                @endcan

                <!-- Subscription Meals -->
                @can('subscription_meal_access')
                <li class="nav-item">
                    <a href="{{ route('admin.subscription-meals.index') }}"
                       class="nav-link {{ request()->is('admin/subscription-meals*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-utensils"></i>
                        <p>{{ trans('cruds.subscriptionMeal.title') }}</p>
                    </a>
                </li>
                @endcan

                <!-- Change Password -->
                @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
                @can('profile_password_edit')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('profile/password*') ? 'active' : '' }}"
                       href="{{ route('profile.password.edit') }}">
                        <i class="nav-icon fas fa-key"></i>
                        <p>{{ trans('global.change_password') }}</p>
                    </a>
                </li>
                @endcan
                @endif

                <!-- Logout -->
                <li class="nav-item">
                    <a href="#" class="nav-link"
                        onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>{{ trans('global.logout') }}</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>

