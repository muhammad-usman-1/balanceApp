{{-- Customer's selected meal extras for print — expects $sm --}}
@if($sm->selectedIngredients->isNotEmpty())
    <div class="meal-extras-line">
        @foreach($sm->selectedIngredients->groupBy('meal_extra_id') as $extraIngredients)
            {{ $extraIngredients->first()->mealExtra?->name ?? 'Extra' }}: {{ $extraIngredients->pluck('name')->implode(', ') }}@if(!$loop->last) &nbsp;·&nbsp; @endif
        @endforeach
    </div>
@endif
