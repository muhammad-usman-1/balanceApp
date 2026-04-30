<aside class="main-sidebar sb-sidebar">

    {{-- ── Brand ── --}}
    <a href="{{ route('admin.home') }}" class="sb-brand">
        <div class="sb-brand-icon">
            <i class="fas fa-leaf"></i>
        </div>
        <div class="sb-brand-text">
            <span class="sb-brand-name">BalanceApp</span>
            <span class="sb-brand-sub">Admin Panel</span>
        </div>
    </a>

    {{-- ── Scrollable nav ── --}}
    <div class="sb-scroll">
        <ul class="nav nav-pills nav-sidebar flex-column sb-nav" data-widget="treeview" role="menu" data-accordion="false">

            {{-- ─ MAIN ─ --}}
            <li class="sb-section-label">Main</li>

            <li class="nav-item sb-item">
                <a class="nav-link sb-link {{ request()->routeIs('admin.home') ? 'active' : '' }}"
                   href="{{ route('admin.home') }}">
                    <span class="sb-icon si-amber"><i class="fas fa-home"></i></span>
                    <span class="sb-label">Dashboard</span>
                </a>
            </li>

            {{-- ─ PEOPLE ─ --}}
            @can('user_management_access')
            <li class="sb-section-label">People</li>

            <li class="nav-item has-treeview sb-item
                {{ request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'menu-open' : '' }}">
                <a class="nav-link sb-link sb-has-sub
                    {{ request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'active' : '' }}"
                   href="#">
                    <span class="sb-icon si-cyan"><i class="fas fa-users"></i></span>
                    <span class="sb-label">{{ trans('cruds.userManagement.title') }}</span>
                    <i class="sb-chevron fas fa-chevron-right"></i>
                </a>
                <ul class="nav nav-treeview sb-submenu">
                    @can('user_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}"
                           class="nav-link sb-sublink {{ request()->is('admin/users*') ? 'active' : '' }}">
                            <i class="fas fa-circle"></i>
                            <span>{{ trans('cruds.user.title') }}</span>
                        </a>
                    </li>
                    @endcan
                    @can('role_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.roles.index') }}"
                           class="nav-link sb-sublink {{ request()->is('admin/roles*') ? 'active' : '' }}">
                            <i class="fas fa-circle"></i>
                            <span>{{ trans('cruds.role.title') }}</span>
                        </a>
                    </li>
                    @endcan
                    @can('permission_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.permissions.index') }}"
                           class="nav-link sb-sublink {{ request()->is('admin/permissions*') ? 'active' : '' }}">
                            <i class="fas fa-circle"></i>
                            <span>{{ trans('cruds.permission.title') }}</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcan

            {{-- ─ CATALOG ─ --}}
            <li class="sb-section-label">Catalog</li>

            @can('meal_access')
            <li class="nav-item sb-item">
                <a href="{{ route('admin.meals.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/meals*') ? 'active' : '' }}">
                    <span class="sb-icon si-orange"><i class="fas fa-utensils"></i></span>
                    <span class="sb-label">{{ trans('cruds.meal.title') }}</span>
                </a>
            </li>
            @endcan

            <li class="nav-item sb-item">
                <a href="{{ route('admin.categories.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                    <span class="sb-icon si-violet"><i class="fas fa-layer-group"></i></span>
                    <span class="sb-label">Categories</span>
                </a>
            </li>

            {{-- ─ LOCATIONS ─ --}}
            <li class="sb-section-label">Locations</li>

            <li class="nav-item has-treeview sb-item
                {{ request()->is('admin/areas*') || request()->is('admin/branches*') ? 'menu-open' : '' }}">
                <a class="nav-link sb-link sb-has-sub
                    {{ request()->is('admin/areas*') || request()->is('admin/branches*') ? 'active' : '' }}"
                   href="#">
                    <span class="sb-icon si-emerald"><i class="fas fa-map-marked-alt"></i></span>
                    <span class="sb-label">Branches &amp; Areas</span>
                    <i class="sb-chevron fas fa-chevron-right"></i>
                </a>
                <ul class="nav nav-treeview sb-submenu">
                    <li class="nav-item">
                        <a href="{{ route('admin.branches.index') }}"
                           class="nav-link sb-sublink {{ request()->is('admin/branches*') ? 'active' : '' }}">
                            <i class="fas fa-circle"></i>
                            <span>Branches</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.areas.index') }}"
                           class="nav-link sb-sublink {{ request()->is('admin/areas*') ? 'active' : '' }}">
                            <i class="fas fa-circle"></i>
                            <span>Areas</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ─ SUBSCRIPTIONS ─ --}}
            <li class="sb-section-label">Subscriptions</li>

            @can('subcrption_plan_access')
            <li class="nav-item sb-item">
                <a href="{{ route('admin.subcrption-plans.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/subcrption-plans*') ? 'active' : '' }}">
                    <span class="sb-icon si-blue"><i class="fas fa-th-large"></i></span>
                    <span class="sb-label">{{ trans('cruds.subcrptionPlan.title') }}</span>
                </a>
            </li>
            @endcan

            @can('user_subcrption_access')
            <li class="nav-item sb-item">
                <a href="{{ route('admin.user-subcrptions.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/user-subcrptions*') ? 'active' : '' }}">
                    <span class="sb-icon si-green"><i class="fas fa-user-check"></i></span>
                    <span class="sb-label">{{ trans('cruds.userSubcrption.title') }}</span>
                </a>
            </li>
            @endcan

            {{-- ─ MARKETING ─ --}}
            <li class="sb-section-label">Marketing</li>

            <li class="nav-item sb-item">
                <a href="{{ route('admin.coupons.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/coupons*') ? 'active' : '' }}">
                    <span class="sb-icon si-pink"><i class="fas fa-ticket-alt"></i></span>
                    <span class="sb-label">Coupons</span>
                </a>
            </li>

            <li class="nav-item sb-item">
                <a href="{{ route('admin.notifications.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                    <span class="sb-icon si-amber"><i class="fas fa-bell"></i></span>
                    <span class="sb-label">Notifications</span>
                </a>
            </li>

            <li class="nav-item sb-item">
                <a href="{{ route('admin.affiliated-codes.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/affiliated-codes*') ? 'active' : '' }}">
                    <span class="sb-icon si-teal"><i class="fas fa-link"></i></span>
                    <span class="sb-label">Affiliated Codes</span>
                </a>
            </li>

            {{-- ─ ACCOUNT ─ --}}
            <li class="sb-section-label">Account</li>

            @if(auth()->user() && auth()->user()->is_admin)
            <li class="nav-item sb-item">
                <a href="{{ route('admin.settings.edit') }}"
                   class="nav-link sb-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <span class="sb-icon si-slate"><i class="fas fa-cog"></i></span>
                    <span class="sb-label">Settings</span>
                </a>
            </li>
            @endif

            @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
            @can('profile_password_edit')
            <li class="nav-item sb-item">
                <a href="{{ route('profile.password.edit') }}"
                   class="nav-link sb-link {{ request()->is('profile/password*') ? 'active' : '' }}">
                    <span class="sb-icon si-rose"><i class="fas fa-key"></i></span>
                    <span class="sb-label">{{ trans('global.change_password') }}</span>
                </a>
            </li>
            @endcan
            @endif

            <li class="nav-item sb-item sb-logout">
                <a href="#" class="nav-link sb-link"
                   onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                    <span class="sb-icon si-red"><i class="fas fa-sign-out-alt"></i></span>
                    <span class="sb-label">{{ trans('global.logout') }}</span>
                </a>
            </li>

        </ul>
    </div>

</aside>
