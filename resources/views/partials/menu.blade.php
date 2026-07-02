<aside class="main-sidebar app-sb" id="appSidebar">

    {{-- Header --}}
    <div class="app-sb__head">
        <a href="{{ route('admin.home') }}" class="app-sb__brand">
            <img src="{{ asset('images/balance-text.png') }}" alt="Balance" class="app-sb__logo">
            <span class="app-sb__sub">{{ $isBranchUser ? ($branchName.' Branch') : 'Admin Panel' }}</span>
        </a>
        <button class="app-sb__close" id="sbClose" aria-label="Close sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Scrollable nav --}}
    <div class="app-sb__body">
        <nav>

            {{-- ─── OVERVIEW (everyone) ─── --}}
            <p class="app-sb__sec">Overview</p>
            <a href="{{ route('admin.home') }}"
               class="app-sb__link {{ request()->routeIs('admin.home') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-blue"><i class="fas fa-th-large"></i></span>
                <span class="app-sb__txt">Dashboard</span>
            </a>

            {{-- ─── OPERATIONS ─── --}}
            @if($isBranchUser)
            <p class="app-sb__sec">{{ $branchName }} Branch</p>
            @else
            <p class="app-sb__sec">Operations</p>
            @endif

            <a href="{{ route('admin.delivery-orders.index') }}"
               class="app-sb__link {{ request()->is('admin/delivery-orders*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-teal"><i class="fas fa-truck"></i></span>
                <span class="app-sb__txt">Delivery Orders</span>
            </a>

            @can('user_subcrption_access')
            <a href="{{ route('admin.user-subcrptions.index') }}"
               class="app-sb__link {{ request()->is('admin/user-subcrptions*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-green"><i class="fas fa-clipboard-list"></i></span>
                <span class="app-sb__txt">Subscriptions</span>
            </a>
            @endcan

            @can('user_subcrption_access')
            @php $pendingCount = \App\Models\SubscriptionPauseRequest::where('status','pending')->count(); @endphp
            <a href="{{ route('admin.pause-requests.index') }}"
               class="app-sb__link {{ request()->is('admin/pause-requests*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-amber"><i class="fas fa-pause-circle"></i></span>
                <span class="app-sb__txt">Pause Requests</span>
                @if($pendingCount > 0)
                <span class="app-sb__badge">{{ $pendingCount }}</span>
                @endif
            </a>
            @endcan

            {{-- ─── APP INQUIRIES (everyone) ─── --}}
            @php $unreadInquiries = \App\Models\AppInquiry::where('status','new')->count(); @endphp
            <a href="{{ route('admin.inquiries.index') }}"
               class="app-sb__link {{ request()->is('admin/inquiries*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-teal"><i class="fas fa-envelope-open-text"></i></span>
                <span class="app-sb__txt">App Inquiries</span>
                @if($unreadInquiries > 0)
                    <span class="app-sb__badge">{{ $unreadInquiries }}</span>
                @endif
            </a>

            {{-- ─── REPORTS (superadmin only) ─── --}}
            @if(!$isBranchUser)
            <p class="app-sb__sec">Reports</p>
            <a href="{{ route('admin.reports.sales') }}"
               class="app-sb__link {{ request()->is('admin/reports/sales*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-teal"><i class="fas fa-receipt"></i></span>
                <span class="app-sb__txt">Sales Report</span>
            </a>
            @endif

            {{-- ─── MEAL CATALOG ─── --}}
            <p class="app-sb__sec">Meal Catalog</p>

            @if($isBranchUser || auth()->user()->can('meal_access'))
            <a href="{{ route('admin.meals.index') }}"
               class="app-sb__link {{ request()->is('admin/meals*') && !request()->is('admin/meal-restrictions*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-orange"><i class="fas fa-utensils"></i></span>
                <span class="app-sb__txt">Meals</span>
            </a>
            @endif

            @if(!$isBranchUser)
            <a href="{{ route('admin.meal-restrictions.index') }}"
               class="app-sb__link {{ request()->is('admin/meal-restrictions*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-red"><i class="fas fa-ban"></i></span>
                <span class="app-sb__txt">Meal Limits</span>
            </a>

            <a href="{{ route('admin.categories.index') }}"
               class="app-sb__link {{ request()->is('admin/categories*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-violet"><i class="fas fa-tags"></i></span>
                <span class="app-sb__txt">Categories</span>
            </a>

            @can('subcrption_plan_access')
            <a href="{{ route('admin.subcrption-plans.index') }}"
               class="app-sb__link {{ request()->is('admin/subcrption-plans*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-indigo"><i class="fas fa-layer-group"></i></span>
                <span class="app-sb__txt">Subscription Plans</span>
            </a>
            @endcan

            <a href="{{ route('admin.protein-options.index') }}"
               class="app-sb__link {{ request()->is('admin/protein-options*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-amber"><i class="fas fa-dumbbell"></i></span>
                <span class="app-sb__txt">Protein Pricing</span>
            </a>

            {{-- ─── LOCATIONS (superadmin only) ─── --}}
            <p class="app-sb__sec">Locations</p>

            <div class="app-sb__tree {{ request()->is('admin/areas*') || request()->is('admin/branches*') ? 'is-open' : '' }}">
                <a href="#" class="app-sb__link app-sb__tree-trigger">
                    <span class="app-sb__ic si-emerald"><i class="fas fa-map-marker-alt"></i></span>
                    <span class="app-sb__txt">Branches &amp; Areas</span>
                    <i class="app-sb__chev fas fa-chevron-right"></i>
                </a>
                <div class="app-sb__sub-list">
                    <a href="{{ route('admin.branches.index') }}"
                       class="app-sb__sub-link {{ request()->is('admin/branches*') ? 'is-active' : '' }}">
                        <i class="fas fa-circle app-sb__dot"></i><span>Branches</span>
                    </a>
                    <a href="{{ route('admin.areas.index') }}"
                       class="app-sb__sub-link {{ request()->is('admin/areas*') ? 'is-active' : '' }}">
                        <i class="fas fa-circle app-sb__dot"></i><span>Areas</span>
                    </a>
                </div>
            </div>

            {{-- ─── MARKETING (superadmin only) ─── --}}
            <p class="app-sb__sec">Marketing</p>

            <a href="{{ route('admin.coupons.index') }}"
               class="app-sb__link {{ request()->is('admin/coupons*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-pink"><i class="fas fa-ticket-alt"></i></span>
                <span class="app-sb__txt">Coupons</span>
            </a>

            <a href="{{ route('admin.notifications.index') }}"
               class="app-sb__link {{ request()->is('admin/notifications*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-amber"><i class="fas fa-bell"></i></span>
                <span class="app-sb__txt">Notifications</span>
            </a>

            <a href="{{ route('admin.affiliated-codes.index') }}"
               class="app-sb__link {{ request()->is('admin/affiliated-codes*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-cyan"><i class="fas fa-link"></i></span>
                <span class="app-sb__txt">Affiliated Codes</span>
            </a>
            @endif {{-- end !$isBranchUser for Locations & Marketing --}}

            {{-- ─── USER PREFERENCES (everyone) ─── --}}
            <p class="app-sb__sec">User Preferences</p>

            <a href="{{ route('admin.user-allergies.index') }}"
               class="app-sb__link {{ request()->is('admin/user-allergies*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-red"><i class="fas fa-exclamation-triangle"></i></span>
                <span class="app-sb__txt">Allergies</span>
            </a>

            <a href="{{ route('admin.user-dislikes.index') }}"
               class="app-sb__link {{ request()->is('admin/user-dislikes*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-orange"><i class="fas fa-thumbs-down"></i></span>
                <span class="app-sb__txt">Dislikes</span>
            </a>

            {{-- ─── USER MANAGEMENT + SYSTEM (superadmin only) ─── --}}
            @if(!$isBranchUser)

            @can('user_management_access')
            <p class="app-sb__sec">User Management</p>

            <div class="app-sb__tree {{ request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'is-open' : '' }}">
                <a href="#" class="app-sb__link app-sb__tree-trigger">
                    <span class="app-sb__ic si-slate"><i class="fas fa-users-cog"></i></span>
                    <span class="app-sb__txt">{{ trans('cruds.userManagement.title') }}</span>
                    <i class="app-sb__chev fas fa-chevron-right"></i>
                </a>
                <div class="app-sb__sub-list">
                    @can('user_access')
                    <a href="{{ route('admin.users.index') }}"
                       class="app-sb__sub-link {{ request()->is('admin/users*') ? 'is-active' : '' }}">
                        <i class="fas fa-circle app-sb__dot"></i><span>{{ trans('cruds.user.title') }}</span>
                    </a>
                    @endcan
                    @can('role_access')
                    <a href="{{ route('admin.roles.index') }}"
                       class="app-sb__sub-link {{ request()->is('admin/roles*') ? 'is-active' : '' }}">
                        <i class="fas fa-circle app-sb__dot"></i><span>{{ trans('cruds.role.title') }}</span>
                    </a>
                    @endcan
                    @can('permission_access')
                    <a href="{{ route('admin.permissions.index') }}"
                       class="app-sb__sub-link {{ request()->is('admin/permissions*') ? 'is-active' : '' }}">
                        <i class="fas fa-circle app-sb__dot"></i><span>{{ trans('cruds.permission.title') }}</span>
                    </a>
                    @endcan
                </div>
            </div>
            @endcan

            @if(auth()->user()?->is_admin)
            <p class="app-sb__sec">System</p>
            <a href="{{ route('admin.settings.edit') }}"
               class="app-sb__link {{ request()->is('admin/settings*') ? 'is-active' : '' }}">
                <span class="app-sb__ic si-slate"><i class="fas fa-cog"></i></span>
                <span class="app-sb__txt">Settings</span>
            </a>
            @endif

            @endif {{-- end !$isBranchUser --}}

        </nav>
    </div>

</aside>
