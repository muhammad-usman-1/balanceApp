{{-- Customer's selected extras for a subscription meal — expects $sm --}}
@if($sm->selectedIngredients->isNotEmpty())
    <div class="meal-extras">
        @foreach($sm->selectedIngredients->groupBy('meal_extra_id') as $extraIngredients)
            <span class="extra-chip">
                <strong>{{ $extraIngredients->first()->mealExtra?->name ?? 'Extra' }}:</strong>
                {{ $extraIngredients->pluck('name')->implode(', ') }}
            </span>
        @endforeach
    </div>
@endif
