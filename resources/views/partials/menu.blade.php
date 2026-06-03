<aside class="main-sidebar sb-sidebar">

    {{-- ── Brand ── --}}
    <a href="{{ route('admin.home') }}" class="sb-brand">
        <div class="sb-brand-logo">
            <img src="{{ asset('images/balance-text.png') }}" alt="Balance">
        </div>
        <span class="sb-brand-sub">Admin Panel</span>
    </a>

    {{-- ── Scrollable nav ── --}}
    <div class="sb-scroll">
        <ul class="nav nav-pills nav-sidebar flex-column sb-nav" data-widget="treeview" role="menu" data-accordion="false">

            {{-- ─── OVERVIEW ─── --}}
            <li class="sb-section-label">Overview</li>

            <li class="nav-item sb-item">
                <a class="nav-link sb-link {{ request()->routeIs('admin.home') ? 'active' : '' }}"
                   href="{{ route('admin.home') }}">
                    <span class="sb-icon si-blue"><i class="fas fa-th-large"></i></span>
                    <span class="sb-label">Dashboard</span>
                </a>
            </li>

            {{-- ─── OPERATIONS ─── --}}
            <li class="sb-section-label">Operations</li>

            <li class="nav-item sb-item">
                <a href="{{ route('admin.delivery-orders.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/delivery-orders*') ? 'active' : '' }}">
                    <span class="sb-icon si-teal"><i class="fas fa-truck"></i></span>
                    <span class="sb-label">Delivery Orders</span>
                </a>
            </li>

            @can('user_subcrption_access')
            <li class="nav-item sb-item">
                <a href="{{ route('admin.user-subcrptions.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/user-subcrptions*') ? 'active' : '' }}">
                    <span class="sb-icon si-green"><i class="fas fa-clipboard-list"></i></span>
                    <span class="sb-label">Subscriptions</span>
                </a>
            </li>
            @endcan

            @can('user_subcrption_access')
            <li class="nav-item sb-item">
                <a href="{{ route('admin.pause-requests.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/pause-requests*') ? 'active' : '' }}">
                    <span class="sb-icon" style="background:#fef3c7; color:#d97706;"><i class="fas fa-pause-circle"></i></span>
                    <span class="sb-label">Pause Requests</span>
                    @php $pendingCount = \App\Models\SubscriptionPauseRequest::where('status','pending')->count(); @endphp
                    @if($pendingCount > 0)
                        <span style="margin-left:auto; background:#d97706; color:#fff; border-radius:10px; padding:1px 7px; font-size:.68rem; font-weight:700;">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
            </li>
            @endcan

            {{-- ─── MEAL CATALOG ─── --}}
            <li class="sb-section-label">Meal Catalog</li>

            @can('meal_access')
            <li class="nav-item sb-item">
                <a href="{{ route('admin.meals.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/meals*') && !request()->is('admin/meal-restrictions*') ? 'active' : '' }}">
                    <span class="sb-icon si-orange"><i class="fas fa-utensils"></i></span>
                    <span class="sb-label">Meals</span>
                </a>
            </li>
            @endcan

            <li class="nav-item sb-item">
                <a href="{{ route('admin.meal-restrictions.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/meal-restrictions*') ? 'active' : '' }}">
                    <span class="sb-icon si-red" style="background:#fee2e2; color:#dc2626;"><i class="fas fa-ban"></i></span>
                    <span class="sb-label">Meal Limits</span>
                </a>
            </li>

            <li class="nav-item sb-item">
                <a href="{{ route('admin.categories.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                    <span class="sb-icon si-violet"><i class="fas fa-tags"></i></span>
                    <span class="sb-label">Categories</span>
                </a>
            </li>

            @can('subcrption_plan_access')
            <li class="nav-item sb-item">
                <a href="{{ route('admin.subcrption-plans.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/subcrption-plans*') ? 'active' : '' }}">
                    <span class="sb-icon si-indigo"><i class="fas fa-layer-group"></i></span>
                    <span class="sb-label">Subscription Plans</span>
                </a>
            </li>
            @endcan

            <li class="nav-item sb-item">
                <a href="{{ route('admin.protein-options.index') }}"
                   class="nav-link sb-link {{ request()->is('admin/protein-options*') ? 'active' : '' }}">
                    <span class="sb-icon si-amber"><i class="fas fa-dumbbell"></i></span>
                    <span class="sb-label">Protein Pricing</span>
                </a>
            </li>

            {{-- ─── LOCATIONS ─── --}}
            <li class="sb-section-label">Locations</li>

            <li class="nav-item has-treeview sb-item
                {{ request()->is('admin/areas*') || request()->is('admin/branches*') ? 'menu-open' : '' }}">
                <a class="nav-link sb-link sb-has-sub
                    {{ request()->is('admin/areas*') || request()->is('admin/branches*') ? 'active' : '' }}"
                   href="#">
                    <span class="sb-icon si-emerald"><i class="fas fa-map-marker-alt"></i></span>
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

            {{-- ─── MARKETING ─── --}}
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
                    <span class="sb-icon si-cyan"><i class="fas fa-link"></i></span>
                    <span class="sb-label">Affiliated Codes</span>
                </a>
            </li>

            {{-- ─── USER MANAGEMENT ─── --}}
            @can('user_management_access')
            <li class="sb-section-label">User Management</li>

            <li class="nav-item has-treeview sb-item
                {{ request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'menu-open' : '' }}">
                <a class="nav-link sb-link sb-has-sub
                    {{ request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'active' : '' }}"
                   href="#">
                    <span class="sb-icon si-slate"><i class="fas fa-users-cog"></i></span>
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

            {{-- ─── SYSTEM ─── --}}
            @if(auth()->user() && auth()->user()->is_admin)
            <li class="sb-section-label">System</li>
            <li class="nav-item sb-item">
                <a href="{{ route('admin.settings.edit') }}"
                   class="nav-link sb-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <span class="sb-icon si-slate"><i class="fas fa-cog"></i></span>
                    <span class="sb-label">Settings</span>
                </a>
            </li>
            @endif

        </ul>
    </div>

</aside>
