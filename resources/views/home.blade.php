@extends('layouts.admin')

@section('content')
<div class="dashboard-modern">
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Welcome Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="dashboard-title">Dashboard</h1>
                <p class="dashboard-subtitle">{{ now()->format('l, F j, Y') }} • {{ now()->format('g:i A') }}</p>
            </div>
            <div class="header-right">
                <div class="quick-stats">
                    <div class="quick-stat-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>{{ $todaySubscriptions }} Today</span>
                    </div>
                    <div class="quick-stat-item">
                        <i class="fas fa-chart-line"></i>
                        <span>{{ $monthlySubscriptions }} This Month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Statistics Cards -->
    <div class="stats-grid">
        <!-- Total Users -->
        <div class="stat-card dark-card" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <h3 class="stat-value">{{ number_format($totalUsers) }}</h3>
                <p class="stat-label">Total Users</p>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('admin.users.index') }}" class="stat-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="stat-card dark-card" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-green">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <h3 class="stat-value">{{ number_format($activeSubscriptions) }}</h3>
                <p class="stat-label">Active Subscriptions</p>
                <p class="stat-sublabel">{{ $totalSubscriptions }} total</p>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('admin.user-subcrptions.index') }}" class="stat-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Meals -->
        <div class="stat-card dark-card" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-orange">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <h3 class="stat-value">{{ number_format($totalMeals) }}</h3>
                <p class="stat-label">Total Meals</p>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('admin.meals.index') }}" class="stat-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Coupons -->
        <div class="stat-card dark-card" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-purple">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <h3 class="stat-value">{{ number_format($totalCoupons) }}</h3>
                <p class="stat-label">Total Coupons</p>
                <p class="stat-sublabel">{{ $activeCoupons }} active</p>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('admin.coupons.index') }}" class="stat-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Secondary Stats Row -->
    <div class="secondary-stats-grid">
        <!-- Payment Status -->
        <div class="info-card dark-card" data-aos="fade-up" data-aos-delay="0">
            <div class="info-card-header">
                <div class="info-icon icon-green">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h5>Payment Status</h5>
            </div>
            <div class="info-card-body">
                <div class="info-item">
                    <div class="info-value-wrapper">
                        <h4>{{ $paidSubscriptions }}</h4>
                        <p>Paid</p>
                    </div>
                    <div class="info-progress">
                        <div class="progress-bar" style="width: {{ $totalSubscriptions > 0 ? ($paidSubscriptions / $totalSubscriptions * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-value-wrapper">
                        <h4>{{ $pendingSubscriptions }}</h4>
                        <p>Pending</p>
                    </div>
                    <div class="info-progress">
                        <div class="progress-bar warning" style="width: {{ $totalSubscriptions > 0 ? ($pendingSubscriptions / $totalSubscriptions * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Activity -->
        <div class="info-card dark-card" data-aos="fade-up" data-aos-delay="100">
            <div class="info-card-header">
                <div class="info-icon icon-blue">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <h5>Today's Activity</h5>
            </div>
            <div class="info-card-body">
                <div class="info-item">
                    <div class="info-value-wrapper">
                        <h4>{{ $todaySubscriptions }}</h4>
                        <p>New Subscriptions</p>
                    </div>
                    <div class="info-badge success">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-value-wrapper">
                        <h4>{{ $todayMeals }}</h4>
                        <p>New Meals</p>
                    </div>
                    <div class="info-badge success">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Overview -->
        <div class="info-card dark-card" data-aos="fade-up" data-aos-delay="200">
            <div class="info-card-header">
                <div class="info-icon icon-orange">
                    <i class="fas fa-cog"></i>
                </div>
                <h5>Management</h5>
            </div>
            <div class="info-card-body">
                <div class="info-item">
                    <div class="info-value-wrapper">
                        <h4>{{ $totalCategories }}</h4>
                        <p>Categories</p>
                    </div>
                    <a href="{{ route('admin.categories.index') }}" class="info-link">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
                <div class="info-item">
                    <div class="info-value-wrapper">
                        <h4>{{ $totalAreas }}</h4>
                        <p>Areas</p>
                    </div>
                    <a href="{{ route('admin.areas.index') }}" class="info-link">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
                <div class="info-item">
                    <div class="info-value-wrapper">
                        <h4>{{ $totalBranches }}</h4>
                        <p>Branches</p>
                    </div>
                    <a href="{{ route('admin.branches.index') }}" class="info-link">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Subscriptions Table -->
    <div class="table-card dark-card" data-aos="fade-up" data-aos-delay="300">
        <div class="table-card-header">
            <div class="table-header-left">
                <div class="table-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h5>Recent Subscriptions</h5>
                    <p>Latest subscription activity</p>
                </div>
            </div>
            <a href="{{ route('admin.user-subcrptions.index') }}" class="btn-view-all">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="table-card-body">
            @if($recentSubscriptions->count() > 0)
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSubscriptions as $subscription)
                                <tr>
                                    <td><span class="table-id">#{{ $subscription->id }}</span></td>
                                    <td>{{ $subscription->user->name ?? 'N/A' }}</td>
                                    <td>{{ $subscription->subcrption_plans->title ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge-modern {{ $subscription->status == 'active' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ ucfirst($subscription->status ?? 'Inactive') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-modern {{ $subscription->payment == 'paid' ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucfirst($subscription->payment ?? 'Pending') }}
                                        </span>
                                    </td>
                                    <td>{{ $subscription->start_date ?? 'N/A' }}</td>
                                    <td>{{ $subscription->end_date ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.user-subcrptions.show', $subscription->id) }}" class="btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No subscriptions found</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Modern Dashboard Styles - Matching Sidebar Theme */
.dashboard-modern {
    padding: 20px;
    background: #f8f9fa;
    min-height: calc(100vh - 60px);
}

/* Dashboard Header */
.dashboard-header {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 25px 30px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.dashboard-title {
    font-size: 2rem;
    font-weight: 700;
    color: #212529;
    margin: 0;
    letter-spacing: -0.5px;
}

.dashboard-subtitle {
    font-size: 0.95rem;
    color: #6c757d;
    margin: 5px 0 0;
}

.quick-stats {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.quick-stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    background: #e9ecef;
    border-radius: 8px;
    color: #495057;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.quick-stat-item:hover {
    background: #dee2e6;
    transform: translateY(-2px);
}

.quick-stat-item i {
    font-size: 0.85rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

/* Light Card Base */
.dark-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e9ecef;
}

.dark-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #dee2e6;
}

/* Stat Card */
.stat-card {
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #007bff;
    transition: width 0.3s ease;
}

.stat-card:hover::before {
    width: 100%;
    opacity: 0.1;
}

.stat-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.stat-icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #ffffff;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-icon-wrapper {
    transform: scale(1.1) rotate(5deg);
}

.icon-blue { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); }
.icon-green { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); }
.icon-orange { background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); }
.icon-purple { background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); }

.stat-trend {
    color: #28a745;
    font-size: 0.9rem;
}

.stat-card-body {
    margin-bottom: 20px;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #212529;
    margin: 0 0 8px;
    line-height: 1;
}

.stat-label {
    font-size: 0.95rem;
    color: #6c757d;
    margin: 0;
    font-weight: 500;
}

.stat-sublabel {
    font-size: 0.8rem;
    color: #868e96;
    margin: 5px 0 0;
}

.stat-card-footer {
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.stat-link {
    color: #495057;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.stat-link:hover {
    color: #212529;
    transform: translateX(5px);
    text-decoration: none;
}

/* Secondary Stats Grid */
.secondary-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

/* Info Card */
.info-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #ffffff;
}

.info-card-header h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
}

.info-card-body {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
}

.info-value-wrapper h4 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #212529;
    margin: 0 0 5px;
}

.info-value-wrapper p {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
}

.info-progress {
    width: 100px;
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: #28a745;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.progress-bar.warning {
    background: #ffc107;
}

.info-badge {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    color: #ffffff;
}

.info-badge.success {
    background: #28a745;
}

.info-link {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
    color: #495057;
    text-decoration: none;
    transition: all 0.3s ease;
}

.info-link:hover {
    background: #dee2e6;
    color: #212529;
    transform: scale(1.1);
}

/* Table Card */
.table-card {
    padding: 0;
    overflow: hidden;
}

.table-card-header {
    padding: 25px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e9ecef;
}

.table-header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.table-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #495057;
    font-size: 1.2rem;
}

.table-header-left h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
}

.table-header-left p {
    margin: 3px 0 0;
    font-size: 0.85rem;
    color: #6c757d;
}

.btn-view-all {
    padding: 8px 16px;
    background: #e9ecef;
    color: #495057;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-view-all:hover {
    background: #dee2e6;
    color: #212529;
    text-decoration: none;
    transform: translateX(3px);
}

.table-card-body {
    padding: 0;
}

.table-responsive {
    overflow-x: auto;
}

.table-modern {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.table-modern thead {
    background: #e9ecef;
}

.table-modern th {
    padding: 15px 20px;
    text-align: left;
    font-size: 0.85rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #dee2e6;
}

.table-modern td {
    padding: 15px 20px;
    color: #212529;
    border-bottom: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.table-modern tbody tr {
    transition: all 0.3s ease;
}

.table-modern tbody tr:hover {
    background: #f8f9fa;
}

.table-id {
    color: #6c757d;
    font-weight: 500;
}

.badge-modern {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge-success {
    background: rgba(40, 167, 69, 0.2);
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.badge-warning {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.badge-secondary {
    background: rgba(108, 117, 125, 0.2);
    color: #6c757d;
    border: 1px solid rgba(108, 117, 125, 0.3);
}

.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #e9ecef;
    color: #495057;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-action:hover {
    background: #dee2e6;
    color: #212529;
    transform: scale(1.1);
}

.empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
    font-size: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-modern {
        padding: 15px;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .secondary-stats-grid {
        grid-template-columns: 1fr;
    }

    .table-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .table-modern {
        font-size: 0.85rem;
    }

    .table-modern th,
    .table-modern td {
        padding: 10px 12px;
    }
}

/* Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dark-card {
    animation: fadeInUp 0.5s ease forwards;
}
</style>

<script>
// Add interactive animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate numbers on scroll
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statValue = entry.target.querySelector('.stat-value');
                if (statValue) {
                    const finalValue = parseInt(statValue.textContent.replace(/,/g, ''));
                    if (!isNaN(finalValue)) {
                        animateValue(statValue, 0, finalValue, 1000);
                    }
                }
            }
        });
    }, observerOptions);

    document.querySelectorAll('.stat-card').forEach(card => {
        observer.observe(card);
    });

    function animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = value.toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
});
</script>
@endsection
