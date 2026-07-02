<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trans('panel.site_title') }} Sign In</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ── Split layout ── */
        .login-wrap {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ── Left panel ── */
        .login-left {
            flex: 0 0 45%;
            background: linear-gradient(160deg, #14532d 0%, #166534 40%, #15803d 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 48px;
            position: relative;
            overflow: hidden;
        }

        /* decorative circles */
        .login-left::before {
            content: '';
            position: absolute;
            width: 480px; height: 480px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,.06);
            top: -120px; left: -120px;
        }
        .login-left::after {
            content: '';
            position: absolute;
            width: 340px; height: 340px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,.07);
            bottom: -80px; right: -80px;
        }

        .login-left-inner { position: relative; z-index: 1; text-align: center; }

        .brand-logo {
            margin-bottom: 28px;
        }
        .brand-logo img {
            max-width: 200px;
            filter: brightness(0) invert(1);
            opacity: .95;
        }

        .brand-tagline {
            color: rgba(255,255,255,.75);
            font-size: .95rem;
            font-weight: 400;
            letter-spacing: .04em;
            margin-bottom: 48px;
        }

        .feature-list { text-align: left; }
        .feature-item {
            display: flex; align-items: center; gap: 12px;
            color: rgba(255,255,255,.8);
            font-size: .85rem; margin-bottom: 16px;
        }
        .feature-item .fi-icon {
            width: 34px; height: 34px; border-radius: 9px;
            background: rgba(255,255,255,.12);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .82rem; flex-shrink: 0;
        }

        /* ── Right panel ── */
        .login-right {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 64px;
        }

        .login-form-wrap { width: 100%; max-width: 380px; }

        .login-heading {
            margin-bottom: 32px;
        }
        .login-heading h1 {
            font-size: 1.6rem; font-weight: 700; color: #111827; margin-bottom: 6px;
        }
        .login-heading p {
            font-size: .88rem; color: #6b7280;
        }

        /* ── Form fields ── */
        .field-group { margin-bottom: 18px; }
        .field-label {
            display: block; font-size: .78rem; font-weight: 600;
            color: #374151; margin-bottom: 6px; letter-spacing: .01em;
        }
        .field-input-wrap { position: relative; }
        .field-input-wrap .field-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; font-size: .82rem; pointer-events: none;
        }
        .field-input {
            width: 100%;
            padding: 11px 14px 11px 38px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: .9rem; color: #111827;
            font-family: inherit;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-input::placeholder { color: #d1d5db; }
        .field-input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22,163,74,.1);
        }
        .field-input.is-invalid { border-color: #ef4444; }
        .invalid-feedback { font-size: .78rem; color: #ef4444; margin-top: 5px; }

        /* ── Remember / forgot ── */
        .login-meta {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 22px;
        }
        .remember-label {
            display: flex; align-items: center; gap: 7px;
            font-size: .82rem; color: #6b7280; cursor: pointer;
        }
        .remember-label input[type="checkbox"] {
            width: 15px; height: 15px; accent-color: #16a34a; cursor: pointer;
        }
        .forgot-link {
            font-size: .82rem; color: #16a34a; text-decoration: none; font-weight: 500;
        }
        .forgot-link:hover { color: #15803d; text-decoration: underline; }

        /* ── Submit button ── */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff; border: none; border-radius: 10px;
            font-size: .92rem; font-weight: 600; font-family: inherit;
            cursor: pointer; letter-spacing: .01em;
            box-shadow: 0 4px 14px rgba(22,163,74,.3);
            transition: opacity .15s, transform .12s, box-shadow .15s;
        }
        .btn-login:hover {
            opacity: .93; transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(22,163,74,.35);
        }
        .btn-login:active { transform: translateY(0); }

        /* ── Alert ── */
        .login-alert {
            padding: 10px 14px; border-radius: 8px; margin-bottom: 18px;
            font-size: .83rem; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;
        }

        /* ── Footer ── */
        .login-footer {
            margin-top: 36px; text-align: center;
            font-size: .75rem; color: #d1d5db;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .login-left { display: none; }
            .login-right { padding: 40px 28px; }
        }
    </style>
</head>
<body>
<div class="login-wrap">

    {{-- ── Left brand panel ── --}}
    <div class="login-left">
        <div class="login-left-inner">
            <div class="brand-logo">
                <img src="{{ asset('images/balance-text.png') }}" alt="Balance">
            </div>
            <p class="brand-tagline">Food for your Life</p>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="fi-icon"><i class="fas fa-truck"></i></div>
                    <span>Manage daily delivery orders with ease</span>
                </div>
                <div class="feature-item">
                    <div class="fi-icon"><i class="fas fa-clipboard-list"></i></div>
                    <span>Track subscriptions and meal plans</span>
                </div>
                <div class="feature-item">
                    <div class="fi-icon"><i class="fas fa-chart-bar"></i></div>
                    <span>Insights on customers and operations</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Right form panel ── --}}
    <div class="login-right">
        <div class="login-form-wrap">

            <div class="login-heading">
                <h1>Welcome back</h1>
                <p>Sign in to your admin account to continue</p>
            </div>

            @if(session()->has('message'))
                <div class="login-alert">
                    <i class="fas fa-info-circle mr-1"></i> {{ session()->get('message') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" autocomplete="off">
                @csrf

                {{-- Email ── --}}
                <div class="field-group">
                    <label class="field-label" for="email">Email Address</label>
                    <div class="field-input-wrap">
                        <i class="fas fa-envelope field-icon"></i>
                        <input id="email" type="email" name="email"
                               class="field-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               value="{{ old('email') }}"
                               placeholder="admin@example.com"
                               required autofocus autocomplete="email">
                    </div>
                    @if($errors->has('email'))
                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                {{-- Password ── --}}
                <div class="field-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-input-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input id="password" type="password" name="password"
                               class="field-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="••••••••"
                               required autocomplete="current-password">
                    </div>
                    @if($errors->has('password'))
                        <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                {{-- Remember + Forgot ── --}}
                <div class="login-meta">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" id="remember">
                        Remember me
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt mr-1"></i> Sign In
                </button>

            </form>

            <div class="login-footer">
                &copy; {{ date('Y') }} Balance · All rights reserved
            </div>

        </div>
    </div>

</div>
</body>
</html>
