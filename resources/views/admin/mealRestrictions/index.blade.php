@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
</style>
<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-ban mr-2" style="color:#dc2626;"></i> Meal Weekly Limits</h3>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success mx-3 mt-3">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="idx-flash idx-flash-error mx-3 mt-3">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Add Restriction Form --}}
    <div style="padding: 20px 20px 0;">
        <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:18px 20px;">
            <p style="font-size:.8rem; font-weight:700; color:#374151; margin-bottom:12px; text-transform:uppercase; letter-spacing:.05em;">
                <i class="fas fa-plus-circle mr-1" style="color:#15803d;"></i> Add Meal Restriction
            </p>
            <form method="POST" action="{{ route('admin.meal-restrictions.store') }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
                @csrf
                <div style="flex:1; min-width:220px;">
                    <label style="font-size:.75rem; font-weight:600; color:#6b7280; display:block; margin-bottom:4px;">Meal</label>
                    <select name="meal_id" class="form-control" style="font-size:.83rem;" required>
                        <option value="">-- Select a meal --</option>
                        @foreach($meals as $meal)
                            <option value="{{ $meal->id }}" {{ old('meal_id') == $meal->id ? 'selected' : '' }}>
                                {{ $meal->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="width:140px;">
                    <label style="font-size:.75rem; font-weight:600; color:#6b7280; display:block; margin-bottom:4px;">Max times / week</label>
                    <input type="number" name="weekly_limit" class="form-control" style="font-size:.83rem;"
                           value="{{ old('weekly_limit', 2) }}" min="1" max="7" required>
                </div>
                <div>
                    <button type="submit" class="idx-btn ib-green" style="height:38px;">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Restrictions Table --}}
    <div class="card-body" style="padding: 20px !important;">
        @if($restrictions->isEmpty())
            <div class="idx-empty">
                <i class="fas fa-ban" style="font-size:2rem; color:#d1d5db;"></i><br>
                No meal restrictions configured yet.<br>
                <span style="font-size:.78rem;">Add a meal above to limit how many times per week it can appear in a user's schedule.</span>
            </div>
        @else
        <div class="table-responsive">
            <table class="table idx-table" style="width:100%; margin:0;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Meal</th>
                        <th>Max per Week</th>
                        <th style="width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($restrictions as $restriction)
                    <tr>
                        <td style="color:#9ca3af; font-size:.75rem;">{{ $restriction->id }}</td>
                        <td>
                            <span style="font-weight:600; color:#111827;">{{ $restriction->meal->title ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="idx-chip chip-red">
                                <i class="fas fa-ban" style="font-size:.6rem;"></i>
                                Max {{ $restriction->weekly_limit }}x / week
                            </span>
                        </td>
                        <td>
                            {{-- Edit trigger --}}
                            <button class="idx-btn ib-edit"
                                    onclick="openEdit({{ $restriction->id }}, {{ $restriction->weekly_limit }})">
                                <i class="fas fa-pen"></i> Edit Limit
                            </button>
                            {{-- Delete --}}
                            <form method="POST"
                                  action="{{ route('admin.meal-restrictions.destroy', $restriction) }}"
                                  style="display:inline;"
                                  onsubmit="return confirm('Remove this restriction?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="idx-btn ib-del">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Edit Limit Modal --}}
<div id="edit-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; padding:28px 28px 24px; width:340px; box-shadow:0 8px 32px rgba(0,0,0,.18);">
        <h5 style="font-size:.95rem; font-weight:700; margin-bottom:18px; color:#111827;">
            <i class="fas fa-pen mr-1" style="color:#b45309;"></i> Edit Weekly Limit
        </h5>
        <form id="edit-form" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label style="font-size:.78rem; font-weight:600; color:#6b7280;">Max times per week</label>
                <input type="number" name="weekly_limit" id="edit-limit" class="form-control"
                       min="1" max="7" required style="font-size:.85rem; margin-top:6px;">
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:18px;">
                <button type="button" class="idx-btn ib-gray" onclick="closeEdit()">Cancel</button>
                <button type="submit" class="idx-btn ib-edit">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, currentLimit) {
    document.getElementById('edit-form').action = '/admin/meal-restrictions/' + id;
    document.getElementById('edit-limit').value = currentLimit;
    const modal = document.getElementById('edit-modal');
    modal.style.display = 'flex';
}
function closeEdit() {
    document.getElementById('edit-modal').style.display = 'none';
}
document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
</script>

@endsection
