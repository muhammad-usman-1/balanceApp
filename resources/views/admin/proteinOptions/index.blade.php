@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Protein Options</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.protein-options.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Protein Option
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Protein (grams)</th>
                                <th>Extra Price / Meal (KWD)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proteinOptions as $option)
                            <tr>
                                <td>{{ $option->id }}</td>
                                <td><strong>{{ $option->protein_grams }}g</strong></td>
                                <td>{{ number_format($option->extra_price_per_meal, 3) }}</td>
                                <td>
                                    <span class="badge badge-{{ $option->is_active ? 'success' : 'secondary' }}">
                                        {{ $option->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.protein-options.edit', $option->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.protein-options.destroy', $option->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this protein option?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No protein options found. Add your first one.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>How pricing works:</strong>
                    Extra charge = <em>Extra Price per Meal</em> &times; <em>Meals per Day</em> &times; <em>Delivery Days per Week</em>.
                    This amount is added on top of the base plan price automatically at checkout.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
