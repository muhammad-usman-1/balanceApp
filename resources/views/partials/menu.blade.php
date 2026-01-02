<aside class="main-sidebar sidebar-dark-primary elevation-4 modern-sidebar">
    <!-- Brand Logo -->
    <a href="{{ route('admin.home') }}" class="brand-link brand-link-modern" style=" text-align: center; ">

        <span class="brand-text font-weight-bold" style=" text-align: center; ">BalanceApp Admin Management</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar sidebar-modern">
        <nav class="mt-3">
            <ul class="nav nav-pills nav-sidebar flex-column modern-nav" data-widget="treeview" role="menu" data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item modern-nav-item">
                    <a class="nav-link modern-nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}"
                       href="{{ route('admin.home') }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                        </div>
                        <p class="nav-text">{{ trans('global.dashboard') }}</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>

                <!-- User Management -->
                @can('user_management_access')
                <li class="nav-item has-treeview modern-nav-item modern-nav-group
                    {{ request()->is('admin/permissions*') ? 'menu-open' : '' }}
                    {{ request()->is('admin/roles*') ? 'menu-open' : '' }}
                    {{ request()->is('admin/users*') ? 'menu-open' : '' }}">

                    <a class="nav-link modern-nav-link modern-nav-parent
                        {{ request()->is('admin/permissions*') ? 'active' : '' }}
                        {{ request()->is('admin/roles*') ? 'active' : '' }}
                        {{ request()->is('admin/users*') ? 'active' : '' }}" href="#">

                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-users"></i>
                        </div>
                        <p class="nav-text">
                            {{ trans('cruds.userManagement.title') }}
                        </p>
                        <i class="right fas fa-chevron-down nav-arrow"></i>
                    </a>

                    <ul class="nav nav-treeview modern-submenu">
                        @can('user_access')
                        <li class="nav-item modern-submenu-item">
                            <a href="{{ route('admin.users.index') }}"
                               class="nav-link modern-submenu-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-dot"></i>
                                <p>{{ trans('cruds.user.title') }}</p>
                            </a>
                        </li>
                        @endcan
                        @can('role_access')
                        <li class="nav-item modern-submenu-item">
                            <a href="{{ route('admin.roles.index') }}"
                               class="nav-link modern-submenu-link {{ request()->is('admin/roles*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-dot"></i>
                                <p>{{ trans('cruds.role.title') }}</p>
                            </a>
                        </li>
                        @endcan
                        @can('permission_access')
                        <li class="nav-item modern-submenu-item">
                            <a href="{{ route('admin.permissions.index') }}"
                               class="nav-link modern-submenu-link {{ request()->is('admin/permissions*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-dot"></i>
                                <p>{{ trans('cruds.permission.title') }}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                <!-- Meals -->
                @can('meal_access')
                <li class="nav-item modern-nav-item">
                    <a href="{{ route('admin.meals.index') }}"
                       class="nav-link modern-nav-link {{ request()->is('admin/meals*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-utensils"></i>
                        </div>
                        <p class="nav-text">{{ trans('cruds.meal.title') }}</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>
                @endcan

                <!-- Categories -->
                <li class="nav-item modern-nav-item">
                    <a href="{{ route('admin.categories.index') }}"
                       class="nav-link modern-nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-list-alt"></i>
                        </div>
                        <p class="nav-text">Categories</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>

                <!-- Branch and Area Management -->
                <li class="nav-item has-treeview modern-nav-item modern-nav-group
                    {{ request()->is('admin/areas*') ? 'menu-open' : '' }}
                    {{ request()->is('admin/branches*') ? 'menu-open' : '' }}">

                    <a class="nav-link modern-nav-link modern-nav-parent
                        {{ request()->is('admin/areas*') ? 'active' : '' }}
                        {{ request()->is('admin/branches*') ? 'active' : '' }}" href="#">

                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-map-marked-alt"></i>
                        </div>
                        <p class="nav-text">
                            Branch and Area Management
                        </p>
                        <i class="right fas fa-chevron-down nav-arrow"></i>
                    </a>

                    <ul class="nav nav-treeview modern-submenu">
                        <li class="nav-item modern-submenu-item">
                            <a href="{{ route('admin.areas.index') }}"
                               class="nav-link modern-submenu-link {{ request()->is('admin/areas*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-dot"></i>
                                <p>Area</p>
                            </a>
                        </li>
                        <li class="nav-item modern-submenu-item">
                            <a href="{{ route('admin.branches.index') }}"
                               class="nav-link modern-submenu-link {{ request()->is('admin/branches*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-dot"></i>
                                <p>Branch</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Subscription Plans -->
                @can('subcrption_plan_access')
                <li class="nav-item modern-nav-item">
                    <a href="{{ route('admin.subcrption-plans.index') }}"
                       class="nav-link modern-nav-link {{ request()->is('admin/subcrption-plans*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-th-large"></i>
                        </div>
                        <p class="nav-text">{{ trans('cruds.subcrptionPlan.title') }}</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>
                @endcan

                <!-- Durations -->
                @can('duration_access')
                <li class="nav-item modern-nav-item">
                    <a href="{{ route('admin.durations.index') }}"
                       class="nav-link modern-nav-link {{ request()->is('admin/durations*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-clock"></i>
                        </div>
                        <p class="nav-text">{{ trans('cruds.duration.title') }}</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>
                @endcan

                <!-- User Subscription -->
                @can('user_subcrption_access')
                <li class="nav-item modern-nav-item">
                    <a href="{{ route('admin.user-subcrptions.index') }}"
                       class="nav-link modern-nav-link {{ request()->is('admin/user-subcrptions*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-user-check"></i>
                        </div>
                        <p class="nav-text">{{ trans('cruds.userSubcrption.title') }}</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>
                @endcan

                <!-- Coupon Management -->
                <li class="nav-item modern-nav-item">
                    <a href="{{ route('admin.coupons.index') }}"
                       class="nav-link modern-nav-link {{ request()->is('admin/coupons*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-ticket-alt"></i>
                        </div>
                        <p class="nav-text">Coupon Management</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>

                <!-- Notification Management -->
                <li class="nav-item modern-nav-item">
                    <a href="{{ route('admin.notifications.index') }}"
                       class="nav-link modern-nav-link {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-bell"></i>
                        </div>
                        <p class="nav-text">Notification Management</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>

                <!-- Affiliated Codes -->
                <li class="nav-item modern-nav-item">
                    <a href="{{ route('admin.affiliated-codes.index') }}"
                       class="nav-link modern-nav-link {{ request()->is('admin/affiliated-codes*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-ticket-alt"></i>
                        </div>
                        <p class="nav-text">Affiliated Codes</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>

                <!-- Divider -->
                <li class="nav-header modern-divider">
                    <span>Settings</span>
                </li>

                <!-- Settings -->
                @if(auth()->user() && auth()->user()->is_admin)
                <li class="nav-item modern-nav-item">
                    <a href="{{ route('admin.settings.edit') }}"
                       class="nav-link modern-nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-cog"></i>
                        </div>
                        <p class="nav-text">Settings</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>
                @endif

                <!-- Change Password -->
                @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
                @can('profile_password_edit')
                <li class="nav-item modern-nav-item">
                    <a class="nav-link modern-nav-link {{ request()->is('profile/password*') ? 'active' : '' }}"
                       href="{{ route('profile.password.edit') }}">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-key"></i>
                        </div>
                        <p class="nav-text">{{ trans('global.change_password') }}</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>
                @endcan
                @endif

                <!-- Logout -->
                <li class="nav-item modern-nav-item modern-nav-logout">
                    <a href="#" class="nav-link modern-nav-link modern-nav-link-logout"
                       onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                        <div class="nav-icon-wrapper">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                        </div>
                        <p class="nav-text">{{ trans('global.logout') }}</p>
                        <span class="nav-badge"></span>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
