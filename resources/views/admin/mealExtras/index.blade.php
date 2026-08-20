@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
    .content-wrapper { background: #fff !important; }
    .me-chip { display:inline-flex; align-items:center; gap:5px; font-size:.7rem; font-weight:700;
               padding:3px 9px; border-radius:20px; }
    .me-single   { background:#eef2ff; color:#4338ca; }
    .me-multiple { background:#ecfeff; color:#0e7490; }
    .me-on  { background:#dcfce7; color:#15803d; }
    .me-off { background:#f3f4f6; color:#6b7280; }
</style>

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-plus-square mr-2" style="color:#0d9488;"></i> Meal Extras</h3>
        <a href="{{ route('admin.meal-extras.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Meal Extra
        </a>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="idx-flash idx-flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="card-body">
        <p style="font-size:.82rem;color:#6b7280;margin:10px 10px 14px;">
            Extra categories (like Bread, Sauce, Topping) and the options inside each. Attach these to meals from the meal form.
        </p>
        <div class="table-responsive">
            <table class="table idx-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Name (Arabic)</th>
                        <th>Selection</th>
                        <th>Required</th>
                        <th>Options</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mealExtras as $extra)
                    <tr>
                        <td style="font-weight:600;color:#111827;">{{ $extra->name }}</td>
                        <td dir="rtl">{{ $extra->name_ar ?? '—' }}</td>
                        <td>
                            <span class="me-chip {{ $extra->selection_type === 'multiple' ? 'me-multiple' : 'me-single' }}">
                                <i class="fas {{ $extra->selection_type === 'multiple' ? 'fa-check-double' : 'fa-check' }}"></i>
                                {{ $extra->selection_type === 'multiple' ? 'Multiple' : 'Single' }}
                            </span>
                        </td>
                        <td>{!! $extra->is_required ? '<span class="me-chip me-on">Required</span>' : '<span class="me-chip me-off">Optional</span>' !!}</td>
                        <td><span class="idx-chip">{{ $extra->ingredients_count }}</span></td>
                        <td>{!! $extra->is_active ? '<span class="me-chip me-on">Active</span>' : '<span class="me-chip me-off">Inactive</span>' !!}</td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.meal-extras.edit', $extra->id) }}" class="idx-btn ib-edit">
                                <i class="fas fa-pen"></i> Manage
                            </a>
                            <form action="{{ route('admin.meal-extras.destroy', $extra->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this extra and all its options?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="idx-empty"><i class="fas fa-plus-square"></i><br>No meal extras yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
