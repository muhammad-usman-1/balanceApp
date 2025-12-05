@extends('layouts.admin')
@section('content')
<div class="content">
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card">
                <div class="welcome-left">
                    <div class="welcome-icon-wrapper">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="welcome-content">
                        <h2 class="welcome-title">Dashboard Overview</h2>
                        <p class="welcome-text">Welcome back! Here's what's happening with your meal subscription system today.</p>
                        <div class="welcome-stats">
                            <div class="welcome-stat-item">
                                <i class="fas fa-calendar-check"></i>
                                <span>{{ now()->format('l, F j, Y') }}</span>
                            </div>
                            <div class="welcome-stat-item">
                                <i class="fas fa-clock"></i>
                                <span>{{ now()->format('g:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="welcome-right">
                    <div class="welcome-decorative-icons">
                        <div class="decorative-icon icon-1">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="decorative-icon icon-2">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="decorative-icon icon-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Total Users -->
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ $totalUsers }}</h3>
                    <p class="stat-label">Total Users</p>
                </div>
                <div class="stat-footer">
                    <a href="{{ route('admin.users.index') }}" class="stat-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Meals -->
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ $totalMeals }}</h3>
                    <p class="stat-label">Total Meals</p>
                </div>
                <div class="stat-footer">
                    <a href="{{ route('admin.meals.index') }}" class="stat-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ $activeSubscriptions }}</h3>
                    <p class="stat-label">Active Subscriptions</p>
                    <small class="stat-sublabel">of {{ $totalSubscriptions }} total</small>
                </div>
                <div class="stat-footer">
                    <a href="{{ route('admin.user-subcrptions.index') }}" class="stat-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Categories -->
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ $totalCategories }}</h3>
                    <p class="stat-label">Categories</p>
                </div>
                <div class="stat-footer">
                    <a href="{{ route('admin.categories.index') }}" class="stat-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Statistics -->
    <div class="row">
        <!-- Today's Activity -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-calendar-day"></i>
                    <h5>Today's Activity</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-icon bg-primary">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="info-content">
                            <h6>{{ $todaySubscriptions }}</h6>
                            <p>New Subscriptions</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon bg-success">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="info-content">
                            <h6>{{ $todayMeals }}</h6>
                            <p>New Meals Added</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-credit-card"></i>
                    <h5>Payment Status</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="info-content">
                            <h6>{{ $paidSubscriptions }}</h6>
                            <p>Paid Subscriptions</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h6>{{ $pendingSubscriptions }}</h6>
                            <p>Pending Payments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Overview -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-chart-bar"></i>
                    <h5>This Month</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-icon bg-info">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="info-content">
                            <h6>{{ $monthlySubscriptions }}</h6>
                            <p>Subscriptions Created</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon bg-secondary">
                            <i class="fas fa-utensil-spoon"></i>
                        </div>
                        <div class="info-content">
                            <h6>{{ $totalMealAssignments }}</h6>
                            <p>Total Meal Assignments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Subscriptions -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Subscriptions
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentSubscriptions->count() > 0)
                        <div class="table-container">
                            <table class="table table-hover">
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
                                            <td>#{{ $subscription->id }}</td>
                                            <td>{{ $subscription->user->name ?? 'N/A' }}</td>
                                            <td>{{ $subscription->subcrption_plans->title ?? 'N/A' }}</td>
                                            <td>
                                                @if($subscription->status == 'active')
                                                    <span class="badge badge-success">{{ ucfirst($subscription->status) }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($subscription->status ?? 'Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($subscription->payment == 'paid')
                                                    <span class="badge badge-success">{{ ucfirst($subscription->payment) }}</span>
                                                @else
                                                    <span class="badge badge-warning">{{ ucfirst($subscription->payment ?? 'Pending') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $subscription->start_date ?? 'N/A' }}</td>
                                            <td>{{ $subscription->end_date ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('admin.user-subcrptions.show', $subscription->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.user-subcrptions.index') }}" class="btn btn-primary">
                                View All Subscriptions <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No subscriptions found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Welcome Card */
.welcome-card {
    background: white;

    padding: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    position: relative;
    overflow: hidden;

    transition: all 0.3s ease;
}

.welcome-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
}

.welcome-card:hover {
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.welcome-left {
    display: flex;
    align-items: center;
    gap: 25px;
    flex: 1;
}

.welcome-icon-wrapper {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.welcome-card:hover .welcome-icon-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: rotate(5deg) scale(1.05);
    border-color: #667eea;
}

.welcome-icon-wrapper i {
    font-size: 2.5rem;
    color: #667eea;
    transition: all 0.3s ease;
}

.welcome-card:hover .welcome-icon-wrapper i {
    color: white;
}

.welcome-content {
    flex: 1;
}

.welcome-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 12px;
    color: #2c3e50;
    letter-spacing: -0.5px;
}

.welcome-text {
    font-size: 1.05rem;
    color: #6c757d;
    margin: 0 0 20px 0;
    line-height: 1.6;
}

.welcome-stats {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
}

.welcome-stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    background: #f8f9fa;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #495057;
    transition: all 0.3s ease;
}

.welcome-stat-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.welcome-stat-item i {
    color: #667eea;
    font-size: 0.9rem;
}

.welcome-right {
    position: relative;
    width: 200px;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.welcome-decorative-icons {
    position: relative;
    width: 100%;
    height: 100%;
}

.decorative-icon {
    position: absolute;
    width: 60px;
    height: 60px;
    background: #f8f9fa;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
    animation: float 3s ease-in-out infinite;
}

.decorative-icon i {
    font-size: 1.5rem;
    color: #667eea;
    transition: all 0.3s ease;
}

.decorative-icon:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: scale(1.1);
    border-color: #667eea;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.decorative-icon:hover i {
    color: white;
}

.icon-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.icon-2 {
    top: 50%;
    right: 10%;
    animation-delay: 1s;
}

.icon-3 {
    bottom: 20%;
    left: 30%;
    animation-delay: 2s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Statistics Cards */
.stat-card {
    background: white;

    padding: 25px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.stat-icon,
.stat-content,
.stat-footer {
    position: relative;
    z-index: 1;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    transition: width 0.3s ease;
    z-index: 0;
    pointer-events: none;
}

.stat-card-primary::before {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card-success::before {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-card-info::before {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.stat-card-warning::before {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.stat-card:hover::before {
    width: 100%;
    opacity: 0.1;
    pointer-events: none;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.stat-card-primary .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card-success .stat-icon {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-card-info .stat-icon {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.stat-card-warning .stat-icon {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    color: #2c3e50;
}

.stat-label {
    font-size: 0.95rem;
    color: #6c757d;
    margin: 5px 0 0;
    font-weight: 500;
}

.stat-sublabel {
    font-size: 0.8rem;
    color: #95a5a6;
    display: block;
    margin-top: 5px;
}

.stat-footer {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
    position: relative;
    z-index: 1;
}

.stat-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    position: relative;
    z-index: 2;
    cursor: pointer;
}

.stat-link:hover {
    color: #764ba2;
    text-decoration: none;
    transform: translateX(5px);
}

/* Info Cards */
.info-card {
    background: white;
    
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    height: 100%;
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.info-card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 2px solid #e9ecef;
}

.info-card-header i {
    font-size: 1.5rem;
    color: #667eea;
}

.info-card-header h5 {
    margin: 0;
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

.info-card-body {
    padding: 20px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}

.info-content h6 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}

.info-content p {
    margin: 5px 0 0;
    color: #6c757d;
    font-size: 0.9rem;
}

/* Table Styling */
.table-container {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
}

.table-container::-webkit-scrollbar {
    display: none;
}

.table-container {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.table {
    margin-bottom: 0;
    width: 100%;
    table-layout: auto;
}

.table thead th {
    border-bottom: 2px solid #e9ecef;
    font-weight: 600;
    color: #2c3e50;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-card {
        flex-direction: column;
        padding: 25px;
    }

    .welcome-left {
        flex-direction: column;
        text-align: center;
        width: 100%;
    }

    .welcome-icon-wrapper {
        width: 70px;
        height: 70px;
    }

    .welcome-icon-wrapper i {
        font-size: 2rem;
    }

    .welcome-title {
        font-size: 1.5rem;
    }

    .welcome-text {
        font-size: 0.95rem;
    }

    .welcome-stats {
        justify-content: center;
        gap: 15px;
    }

    .welcome-right {
        width: 100%;
        height: 150px;
        margin-top: 20px;
    }

    .decorative-icon {
        width: 50px;
        height: 50px;
    }

    .decorative-icon i {
        font-size: 1.2rem;
    }

    .stat-value {
        font-size: 2rem;
    }

    .info-card-header {
        padding: 15px;
    }

    .info-card-body {
        padding: 15px;
    }
}
</style>
@endsection
@section('scripts')
@parent

@endsection
