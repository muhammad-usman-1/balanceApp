<div class="subscription-details-container">
    <!-- Subscription Information -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Subscription Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="40%">Subscription ID:</th>
                            <td><strong>#{{ $userSubcrption->id }}</strong></td>
                        </tr>
                        <tr>
                            <th>User:</th>
                            <td>{{ $userSubcrption->user->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Plan:</th>
                            <td>{{ $userSubcrption->subcrption_plans->title ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Duration:</th>
                            <td>{{ $userSubcrption->duration->title ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Selected Days:</th>
                            <td>
                                @if($userSubcrption->selected_days)
                                    @php
                                        $days = is_array($userSubcrption->selected_days) 
                                            ? $userSubcrption->selected_days 
                                            : explode(',', $userSubcrption->selected_days);
                                    @endphp
                                    @foreach($days as $day)
                                        {{ ucfirst(trim($day)) }}@if(!$loop->last),<br>@endif
                                    @endforeach
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="40%">Start Date:</th>
                            <td>{{ $userSubcrption->start_date ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>End Date:</th>
                            <td>{{ $userSubcrption->end_date ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Price:</th>
                            <td><strong>${{ number_format($userSubcrption->price ?? 0, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <td>
                                @if($userSubcrption->payment == 'paid')
                                    <span class="badge badge-success">{{ ucfirst($userSubcrption->payment) }}</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($userSubcrption->payment ?? 'Pending') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($userSubcrption->status == 'active')
                                    <span class="badge badge-success">{{ ucfirst($userSubcrption->status) }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($userSubcrption->status ?? 'Inactive') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Personalized:</th>
                            <td>
                                @if($userSubcrption->is_personalized)
                                    <span class="badge badge-info">Yes</span>
                                    @if($userSubcrption->protein || $userSubcrption->carbs)
                                        <small class="text-muted">(Protein: {{ $userSubcrption->protein ?? '-' }}, Carbs: {{ $userSubcrption->carbs ?? '-' }})</small>
                                    @endif
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Days and Meals -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Subscription Days & Meals</h5>
        </div>
        <div class="card-body">
            @if($subscriptionDays->count() > 0)
                <div class="row">
                    @foreach($subscriptionDays as $subscriptionDay)
                        <div class="col-md-6 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-capitalize">
                                        <i class="fas fa-calendar-day"></i> {{ ucfirst($subscriptionDay->day) }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($subscriptionDay->subscription_meals->count() > 0)
                                        <div class="meals-list">
                                            @foreach($subscriptionDay->subscription_meals as $subscriptionMeal)
                                                <div class="meal-item mb-3 p-2 border rounded">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">
                                                                {{ $subscriptionMeal->meal->title ?? 'N/A' }}
                                                                <span class="badge badge-{{ $subscriptionMeal->type == 'is meal' ? 'primary' : 'warning' }} badge-sm ml-2">
                                                                    {{ $subscriptionMeal->type == 'is meal' ? 'Meal' : 'Snack' }}
                                                                </span>
                                                            </h6>
                                                            @if($subscriptionMeal->meal)
                                                                <div class="meal-details text-muted small">
                                                                    @if($subscriptionMeal->meal->description)
                                                                        <p class="mb-1">{{ Str::limit($subscriptionMeal->meal->description, 100) }}</p>
                                                                    @endif
                                                                    <div class="nutrition-info">
                                                                        @if($subscriptionMeal->meal->calories)
                                                                            <span class="mr-2"><i class="fas fa-fire"></i> {{ $subscriptionMeal->meal->calories }} cal</span>
                                                                        @endif
                                                                        @if($subscriptionMeal->meal->protein_g)
                                                                            <span class="mr-2"><i class="fas fa-dumbbell"></i> {{ $subscriptionMeal->meal->protein_g }}g protein</span>
                                                                        @endif
                                                                        @if($subscriptionMeal->meal->carbs_g)
                                                                            <span class="mr-2"><i class="fas fa-bread-slice"></i> {{ $subscriptionMeal->meal->carbs_g }}g carbs</span>
                                                                        @endif
                                                                        @if($subscriptionMeal->meal->fat_g)
                                                                            <span class="mr-2"><i class="fas fa-oil-can"></i> {{ $subscriptionMeal->meal->fat_g }}g fat</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-utensils-slash"></i> No meals assigned for this day
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No subscription days found for this subscription.
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.meal-item {
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.meal-item:hover {
    background-color: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nutrition-info {
    margin-top: 5px;
}

.badge-sm {
    font-size: 0.75rem;
    padding: 0.25em 0.5em;
}

.subscription-details-container {
    max-height: 70vh;
    overflow-y: auto;
}
</style>

