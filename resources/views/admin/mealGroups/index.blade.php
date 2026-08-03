@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }

/* Search */
.mg-search { position: relative; max-width: 360px; margin-bottom: 18px; }
.mg-search input {
    width: 100%; padding: 8px 12px 8px 32px; border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: .84rem; outline: none; color: #111827;
}
.mg-search input:focus { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.08); }
.mg-search .fa-search { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: .78rem; }

/* Group cards */
.mg-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; }
.mg-card {
    border: 1px solid #e5e7eb; border-radius: 14px; background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.04); display: flex; flex-direction: column; overflow: hidden;
}
.mg-card-head {
    padding: 14px 16px 12px; border-bottom: 1px solid #f3f4f6;
    display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;
}
.mg-card-title { font-size: .95rem; font-weight: 700; color: #111827; line-height: 1.3; }
.mg-card-sub { font-size: .72rem; color: #9ca3af; margin-top: 2px; }
.mg-card-actions { display: flex; gap: 6px; flex-shrink: 0; }
.mg-card-body { padding: 12px 16px; flex: 1; }
.mg-meal-row {
    display: flex; align-items: center; gap: 9px; padding: 5px 0;
}
.mg-meal-thumb {
    width: 30px; height: 30px; border-radius: 6px; object-fit: cover; border: 1px solid #e5e7eb; flex-shrink: 0;
}
.mg-meal-thumb-ph {
    width: 30px; height: 30px; border-radius: 6px; flex-shrink: 0;
    background: linear-gradient(135deg,#fee2e2,#fecaca);
    display: inline-flex; align-items: center; justify-content: center; color: #dc2626; font-size: .68rem;
}
.mg-meal-name { font-size: .82rem; color: #374151; font-weight: 500; }
.mg-meal-cat { font-size: .68rem; color: #9ca3af; }
.mg-card-empty { color: #d1d5db; font-size: .8rem; font-style: italic; padding: 6px 0; }
.mg-more { font-size: .74rem; color: #dc2626; font-weight: 600; margin-top: 4px; cursor: pointer; }

/* Ungrouped meals panel */
.mg-ungrouped { display: flex; flex-wrap: wrap; gap: 6px; }
.mg-meal-chip {
    display: inline-flex; align-items: center; gap: 5px; font-size: .74rem; background: #f9fafb;
    border: 1px solid #f3f4f6; color: #374151; padding: 3px 10px 3px 4px; border-radius: 20px;
}
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-layer-group mr-2" style="color:#dc2626;"></i> Meal Groups</h3>
        <a href="{{ route('admin.meal-groups.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Meal Group
        </a>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success" style="margin:16px 20px 0;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card-body" style="margin-left: 10px;">
        <p style="font-size:.8rem; color:#6b7280; margin-bottom:16px;">
            Meals inside a group share one weekly limit. E.g. a group of 10 meals with a limit of 2 means a user
            can pick any combination of only 2 meals from that group per week. Meals not in any group have no
            weekly restriction.
        </p>

        @if($groups->count() > 3)
        <div class="mg-search">
            <i class="fas fa-search"></i>
            <input type="text" id="mgGroupSearch" placeholder="Filter groups by name or meal…">
        </div>
        @endif

        <div class="mg-grid" id="mgGrid">
            @forelse($groups as $group)
            @php $mealNames = $group->meals->pluck('title')->implode(' '); @endphp
            <div class="mg-card" data-search="{{ strtolower($group->name.' '.$mealNames) }}">
                <div class="mg-card-head">
                    <div>
                        <div class="mg-card-title">{{ $group->name }}</div>
                        <div class="mg-card-sub">{{ $group->meals_count }} meal(s) in this group</div>
                    </div>
                    <div class="mg-card-actions">
                        <span class="idx-chip chip-red" style="white-space:nowrap;">
                            <i class="fas fa-ban" style="font-size:.55rem;"></i> Max {{ $group->weekly_limit }}x/wk
                        </span>
                    </div>
                </div>
                <div class="mg-card-body">
                    @forelse($group->meals->take(4) as $meal)
                        @php $thumb = $meal->getFirstMedia('image'); @endphp
                        <div class="mg-meal-row">
                            @if($thumb)
                                <img src="{{ $thumb->getUrl('thumb') ?: $thumb->getUrl() }}" class="mg-meal-thumb" alt="">
                            @else
                                <div class="mg-meal-thumb-ph"><i class="fas fa-utensils"></i></div>
                            @endif
                            <div>
                                <div class="mg-meal-name">{{ $meal->title }}</div>
                                @if($meal->category?->name)
                                    <div class="mg-meal-cat">{{ $meal->category->name }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="mg-card-empty">No meals assigned yet.</div>
                    @endforelse
                    @if($group->meals_count > 4)
                        <div class="mg-more">+ {{ $group->meals_count - 4 }} more</div>
                    @endif
                </div>
                <div style="padding:10px 16px; border-top:1px solid #f3f4f6; display:flex; gap:8px;">
                    <a href="{{ route('admin.meal-groups.edit', $group) }}" class="idx-btn ib-edit" style="flex:1; justify-content:center;">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('admin.meal-groups.destroy', $group) }}"
                          onsubmit="return confirm('Delete this group? Its meals will become unrestricted.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            @empty
            <div class="idx-empty" style="grid-column:1/-1;">
                <i class="fas fa-layer-group" style="font-size:2rem; color:#d1d5db;"></i><br>
                No meal groups configured yet.<br>
                <span style="font-size:.78rem;">Create one to start limiting how many meals from a category a user can pick per week.</span>
            </div>
            @endforelse
        </div>
        <div id="mgNoResults" class="idx-empty" style="display:none;">
            <i class="fas fa-search" style="font-size:1.6rem; color:#e5e7eb;"></i><br>
            No groups match your search.
        </div>

        @if($ungroupedMeals->count())
        <div style="margin-top:26px; padding-top:18px; border-top:1px solid #f3f4f6;">
            <p style="font-size:.72rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px;">
                <i class="fas fa-infinity mr-1"></i> Meals with no weekly restriction ({{ $ungroupedMeals->count() }})
            </p>
            <div class="mg-ungrouped">
                @foreach($ungroupedMeals as $meal)
                    <span class="mg-meal-chip">
                        @if($meal->category?->name)
                            <span style="color:#9ca3af;">{{ $meal->category->name }}:</span>
                        @endif
                        {{ $meal->title }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
@section('scripts')
@parent
<script>
(function() {
    var input = document.getElementById('mgGroupSearch');
    if (!input) return;
    var cards = Array.prototype.slice.call(document.querySelectorAll('#mgGrid .mg-card'));
    var noResults = document.getElementById('mgNoResults');

    input.addEventListener('input', function() {
        var term = this.value.trim().toLowerCase();
        var visibleCount = 0;
        cards.forEach(function(card) {
            var match = card.dataset.search.indexOf(term) !== -1;
            card.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });
        noResults.style.display = (term && visibleCount === 0) ? 'block' : 'none';
    });
})();
</script>
@endsection
