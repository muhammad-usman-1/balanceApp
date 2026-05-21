@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-dumbbell mr-2" style="color:#d97706;"></i> Protein Pricing</h3>
        <a href="{{ route('admin.protein-options.create') }}" class="idx-btn ib-green">
            <i class="fas fa-plus"></i> Add Option
        </a>
    </div>

    @if(session('success'))
        <div class="idx-flash idx-flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="idx-flash idx-flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table idx-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Protein (g)</th>
                        <th>Extra Price / Meal (KWD)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proteinOptions as $option)
                    <tr>
                        <td style="font-weight:600;color:#111827;">#{{ $option->id }}</td>
                        <td>
                            <span style="font-weight:700;font-size:.95rem;">{{ $option->protein_grams }}</span>
                            <span style="color:#9ca3af;font-size:.78rem;">g</span>
                        </td>
                        <td>
                            <span style="font-weight:700;">{{ number_format($option->extra_price_per_meal, 3) }}</span>
                            <span style="color:#9ca3af;font-size:.78rem;"> KWD</span>
                        </td>
                        <td>
                            @if($option->is_active)
                                <span class="idx-chip chip-green"><i class="fas fa-circle" style="font-size:.4rem;"></i> Active</span>
                            @else
                                <span class="idx-chip chip-gray">Inactive</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.protein-options.edit', $option->id) }}" class="idx-btn ib-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <form action="{{ route('admin.protein-options.destroy', $option->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this protein option?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="idx-btn ib-del"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="idx-empty"><i class="fas fa-dumbbell"></i><br>No protein options found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin:14px 20px; padding:10px 14px; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:8px; font-size:.8rem;">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>Pricing formula:</strong> Extra charge = Extra Price per Meal &times; Meals per Day &times; Delivery Days per Week — added on top of the base plan price at checkout.
        </div>
    </div>
</div>

@endsection
