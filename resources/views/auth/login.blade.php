@extends('layouts.app')

@section('content')
<div class="login-container">
    <div class="login-wrapper">
        <!-- Left Side - Branding -->
        <div class="login-left">
            <div class="login-brand">
                <div class="brand-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1 class="brand-title">{{ trans('panel.site_title') }}</h1>
                <p class="brand-subtitle">Admin Panel</p>
            </div>
            <div class="login-features">
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure Access</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard Analytics</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-cog"></i>
                    <span>Full Control</span>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-card">
                <div class="login-header">
                    <h2>Login Here</h2>
                    <p>Welcome back! Please login to your account.</p>
                </div>

                @if(session()->has('message'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session()->get('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="login-form">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> {{ trans('global.login_email') }}
                        </label>
                        <input id="email"
                               type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="email"
                               autofocus
                               placeholder="Enter your email">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="password-input-wrapper">
                            <input id="password"
                                   type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Enter your password">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group form-options">

                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-password">
                                {{ trans('global.forgot_password') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn-login">
                        <span>{{ trans('global.login') }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Login Page Styles */
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;

    padding: 20px;
}

.login-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1000px;
    width: 100%;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    min-height: 600px;
}

/* Left Side - Branding */
.login-left {
    background: linear-gradient(135deg, #343a40 0%, #495057 100%);
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: white;
}

.login-brand {
    text-align: center;
}

.brand-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2.5rem;
    backdrop-filter: blur(10px);
}

.brand-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 10px;
    letter-spacing: -0.5px;
}

.brand-subtitle {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.7);
    margin: 0;
}

.login-features {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 40px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.feature-item:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(5px);
}

.feature-item i {
    font-size: 1.5rem;
    width: 30px;
    text-align: center;
}

.feature-item span {
    font-size: 0.95rem;
    font-weight: 500;
}

/* Right Side - Login Form */
.login-right {
    padding: 60px 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-card {
    width: 100%;
    max-width: 400px;
}

.login-header {
    text-align: center;
    margin-bottom: 40px;
}

.login-header h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #212529;
    margin: 0 0 10px;
    letter-spacing: -0.5px;
}

.login-header p {
    color: #6c757d;
    margin: 0;
    font-size: 0.95rem;
}

.login-form {
    width: 100%;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.form-label i {
    color: #667eea;
    font-size: 0.9rem;
}

.form-control {
    height: 50px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.form-control:focus {
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    outline: none;
}

.form-control::placeholder {
    color: #adb5bd;
}

.password-input-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
    font-size: 1rem;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #667eea;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
    margin: 0;
}

.form-check-label {
    font-size: 0.9rem;
    color: #495057;
    cursor: pointer;
    margin: 0;
    font-weight: 500;
}

.forgot-password {
    color: #667eea;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.3s ease;
}

.forgot-password:hover {
    color: #764ba2;
    text-decoration: none;
}

.btn-login {
    width: 100%;
    height: 50px;
    background: linear-gradient(135deg, #343a40 0%, #495057 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(33, 37, 41, 0.3);
}




.alert {
    border-radius: 10px;
    margin-bottom: 25px;
    padding: 12px 15px;
    border: none;
}

.alert-info {
    background: #e7f3ff;
    color: #004085;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-wrapper {
        grid-template-columns: 1fr;
        max-width: 100%;
        border-radius: 0;
        min-height: 100vh;
    }

    .login-left {
        padding: 40px 30px;
        min-height: 300px;
    }

    .login-right {
        padding: 40px 30px;
    }

    .brand-icon {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }

    .brand-title {
        font-size: 1.5rem;
    }

    .login-features {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }

    .feature-item {
        flex: 1;
        min-width: 120px;
        padding: 10px;
        flex-direction: column;
        text-align: center;
    }

    .feature-item span {
        font-size: 0.85rem;
    }

    .login-header h2 {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .login-container {
        padding: 0;
    }

    .login-left {
        padding: 30px 20px;
    }

    .login-right {
        padding: 30px 20px;
    }
}
</style>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('password-toggle-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
@endsection
