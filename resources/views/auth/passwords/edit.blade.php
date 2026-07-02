@extends('layouts.admin')
@section('content')

@include('partials.idx-styles')
<style>
.profile-label {
    font-size: .8rem; font-weight: 600; color: #374151; margin-bottom: 5px; display: block;
}
.profile-input {
    width: 100%; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: .87rem; color: #111827; outline: none; transition: border-color .15s, box-shadow .15s;
    background: #fff;
}
.profile-input:focus {
    border-color: #111827; box-shadow: 0 0 0 3px rgba(17,24,39,.07);
}
.profile-input.is-invalid { border-color: #ef4444; }
.profile-err { font-size: .75rem; color: #ef4444; margin-top: 4px; }
.profile-group { margin-bottom: 18px; }
.content-wrapper { background: #fff !important; }
.profile-section-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .9rem; flex-shrink: 0;
}
</style>

@if(session('message'))
<div class="idx-flash idx-flash-success" style="margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> {{ session('message') }}
</div>
@endif

<div class="row">

    {{-- My Profile --}}
    <div class="col-lg-6 mb-4">
        <div class="card idx-card">
            <div class="card-header">
                <h3>
                    <span class="profile-section-icon" style="background:#dbeafe; color:#1d4ed8; margin-right:8px;">
                        <i class="fas fa-user"></i>
                    </span>
                    {{ trans('global.my_profile') }}
                </h3>
            </div>
            <div class="card-body" style="padding: 24px 24px !important;">
                <form method="POST" action="{{ route('profile.password.updateProfile') }}">
                    @csrf
                    <div class="profile-group">
                        <label class="profile-label" for="name">
                            {{ trans('cruds.user.fields.name') }} <span style="color:#ef4444;">*</span>
                        </label>
                        <input class="profile-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               type="text" name="name" id="name"
                               value="{{ old('name', auth()->user()->name) }}" required>
                        @if($errors->has('name'))
                            <div class="profile-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('name') }}</div>
                        @endif
                    </div>
                    <div class="profile-group">
                        <label class="profile-label" for="email">
                            {{ trans('cruds.user.fields.email') }} <span style="color:#ef4444;">*</span>
                        </label>
                        <input class="profile-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               type="email" name="email" id="email"
                               value="{{ old('email', auth()->user()->email) }}" required>
                        @if($errors->has('email'))
                            <div class="profile-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('email') }}</div>
                        @endif
                    </div>
                    <button type="submit" class="idx-btn ib-view" style="padding:8px 20px; font-size:.83rem;">
                        <i class="fas fa-save"></i> {{ trans('global.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="col-lg-6 mb-4">
        <div class="card idx-card">
            <div class="card-header">
                <h3>
                    <span class="profile-section-icon" style="background:#dcfce7; color:#15803d; margin-right:8px;">
                        <i class="fas fa-lock"></i>
                    </span>
                    {{ trans('global.change_password') }}
                </h3>
            </div>
            <div class="card-body" style="padding: 24px 24px !important;">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    <div class="profile-group">
                        <label class="profile-label" for="password">
                            New {{ trans('cruds.user.fields.password') }} <span style="color:#ef4444;">*</span>
                        </label>
                        <input class="profile-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               type="password" name="password" id="password" required>
                        @if($errors->has('password'))
                            <div class="profile-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('password') }}</div>
                        @endif
                    </div>
                    <div class="profile-group">
                        <label class="profile-label" for="password_confirmation">
                            Confirm New {{ trans('cruds.user.fields.password') }} <span style="color:#ef4444;">*</span>
                        </label>
                        <input class="profile-input {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                               type="password" name="password_confirmation" id="password_confirmation" required>
                    </div>
                    <button type="submit" class="idx-btn ib-green" style="padding:8px 20px; font-size:.83rem;">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- Delete Account --}}
@if(!$isBranchUser)
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card idx-card" style="border-color:#fee2e2 !important;">
            <div class="card-header" style="background:#fff5f5 !important; border-color:#fee2e2 !important;">
                <h3>
                    <span class="profile-section-icon" style="background:#fee2e2; color:#dc2626; margin-right:8px;">
                        <i class="fas fa-user-times"></i>
                    </span>
                    {{ trans('global.delete_account') }}
                </h3>
            </div>
            <div class="card-body" style="padding: 20px 24px !important;">
                <p style="font-size:.83rem; color:#6b7280; margin-bottom:16px;">
                    Once you delete your account, all data will be permanently removed. This action cannot be undone.
                </p>
                <form method="POST" action="{{ route('profile.password.destroyProfile') }}"
                      onsubmit="return prompt('{{ __('global.delete_account_warning') }}') == '{{ auth()->user()->email }}'">
                    @csrf
                    <button type="submit" class="idx-btn ib-del" style="padding:8px 20px; font-size:.83rem;">
                        <i class="fas fa-trash-alt"></i> {{ trans('global.delete') }} Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
