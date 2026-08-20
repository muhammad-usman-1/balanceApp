@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
@include('admin.mealExtras._styles')

<div class="card idx-card">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle mr-2" style="color:#22c55e;"></i> Add Meal Extra</h3>
        <a href="{{ route('admin.meal-extras.index') }}" class="idx-btn ib-gray">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="idx-flash idx-flash-error" style="margin:16px 20px 0;">
        <i class="fas fa-exclamation-circle"></i> Please fix the errors below before saving.
    </div>
    @endif

    <div class="card-body" style="padding: 24px !important;">
        <form action="{{ route('admin.meal-extras.store') }}" method="POST" style="max-width:680px;">
            @csrf
            <p class="mf-section"><i class="fas fa-plus-square mr-1"></i> Extra Details</p>

            @include('admin.mealExtras._fields')

            <div style="margin-top: 12px; padding-top: 20px; border-top: 1px solid #f3f4f6; display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="idx-btn ib-green" style="padding: 9px 22px; font-size: .85rem;">
                    <i class="fas fa-check"></i> Save Extra
                </button>
                <a href="{{ route('admin.meal-extras.index') }}" class="idx-btn ib-gray" style="padding: 9px 18px; font-size: .85rem;">Cancel</a>
            </div>
            <p style="font-size:.78rem;color:#9ca3af;margin-top:14px;">
                <i class="fas fa-info-circle"></i> After saving you can add the options (e.g. Brown Bread, Whole Wheat) on the next screen.
            </p>
        </form>
    </div>
</div>

@endsection
