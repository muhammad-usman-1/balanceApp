@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
.meal-thumb {
    width: 42px; height: 42px; border-radius: 8px; object-fit: cover; border: 1px solid #e5e7eb;
}
.meal-thumb-placeholder {
    width: 42px; height: 42px; border-radius: 8px;
    background: linear-gradient(135deg,#e0e7ff,#c7d2fe);
    display: inline-flex; align-items: center; justify-content: center;
    color: #6366f1; font-size: .8rem;
}
.macro-pill {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: .68rem; color: #6b7280; background: #f3f4f6;
    padding: 2px 7px; border-radius: 20px; margin: 1px 2px 1px 0;
}
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-utensils mr-2" style="color:#ea580c;"></i> Meals</h3>
        @can('meal_create')
        <a href="{{ route('admin.meals.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Meal
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div style="margin:16px 20px 0; padding:10px 14px; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; border-radius:8px; font-size:.83rem;">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table datatable datatable-Meal" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Calories</th>
                        <th>Macros</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meals as $meal)
                    @php
                        $image = $meal->getFirstMedia('image');
                        $thumbUrl = $image ? $image->getUrl('thumb') : null;
                        $imgUrl   = $image ? $image->getUrl() : null;
                        if ($thumbUrl === $imgUrl) { $thumbUrl = $imgUrl; }
                        $category = $meal->category_id ? $meal->category : null;
                    @endphp
                    <tr data-entry-id="{{ $meal->id }}">
                        <td data-order="{{ $meal->id }}" style="font-weight:600; color:#111827;">#{{ $meal->id }}</td>
                        <td>
                            @if($thumbUrl)
                                <img src="{{ $thumbUrl }}" alt="{{ $meal->title }}" class="meal-thumb"
                                     onerror="this.onerror=null;this.src='{{ $imgUrl }}';">
                            @else
                                <div class="meal-thumb-placeholder"><i class="fas fa-utensils"></i></div>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight:600;">{{ $meal->title ?? '—' }}</span>
                            @if($meal->description)
                                <div style="font-size:.72rem; color:#9ca3af; margin-top:2px;">
                                    {{ Str::limit($meal->description, 60) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($category && $category->name)
                                <span class="idx-chip chip-violet">{{ $category->name }}</span>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($meal->type === 'is meal')
                                <span class="idx-chip chip-blue">Meal</span>
                            @elseif($meal->type === 'is snack')
                                <span class="idx-chip chip-orange">Snack</span>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($meal->calories)
                                <span style="font-weight:600;">{{ $meal->calories }}</span>
                                <span style="font-size:.72rem; color:#9ca3af;"> kcal</span>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($meal->protein_g)
                                <span class="macro-pill"><i class="fas fa-dumbbell"></i> {{ $meal->protein_g }}g</span>
                            @endif
                            @if($meal->carbs_g)
                                <span class="macro-pill"><i class="fas fa-bread-slice"></i> {{ $meal->carbs_g }}g</span>
                            @endif
                            @if($meal->fat_g)
                                <span class="macro-pill"><i class="fas fa-tint"></i> {{ $meal->fat_g }}g</span>
                            @endif
                            @if(!$meal->protein_g && !$meal->carbs_g && !$meal->fat_g)
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($meal->is_active)
                                <span class="idx-chip chip-green"><i class="fas fa-circle" style="font-size:.4rem;"></i> Active</span>
                            @else
                                <span class="idx-chip chip-gray">Inactive</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            @can('meal_show')
                            <a href="{{ route('admin.meals.show', $meal->id) }}" class="idx-btn ib-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @endcan
                            @can('meal_edit')
                            <a href="{{ route('admin.meals.edit', $meal->id) }}" class="idx-btn ib-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            @endcan
                            @can('meal_delete')
                            <form action="{{ route('admin.meals.destroy', $meal->id) }}" method="POST"
                                  onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
@section('scripts')
@parent
<script>
$(function () {
    $('.datatable-Meal:not(.ajaxTable)').DataTable({
        buttons: [],
        dom: 'lfrtp',
        order: [[2, 'asc']],
        pageLength: 25,
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { orderable: false, targets: -1 }
        ],
        select: false
    });
});
</script>
@endsection
