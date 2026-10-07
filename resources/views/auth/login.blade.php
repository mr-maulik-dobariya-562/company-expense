<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" href="{{ asset('icons/icon.svg') }}" type="image/svg+xml">
    <title>Login | Raging Developers Expense Management</title>
    @include('partials.theme-head')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/glass.css') }}">
    <link rel="stylesheet" href="{{ asset('css/motion.css') }}">
</head>
<body class="hold-transition login-page">
@include('partials.theme-toggle', ['class' => 'login-theme-toggle'])
<div class="login-orbs" aria-hidden="true"><i></i><i></i><i></i></div>

<div class="login-stage" id="loginStage">
    <div class="login-box">
        <div class="login-logo li-item" style="--d:0">
            <div class="lock-badge" id="lockBadge" aria-hidden="true">
                <svg viewBox="0 0 48 48" class="lock-svg">
                    <path class="lock-shackle" d="M15 22 V15 a9 9 0 0 1 18 0 V22" />
                    <rect class="lock-body" x="10" y="21" width="28" height="21" rx="6" />
                    <circle class="lock-hole" cx="24" cy="31.5" r="2.6" />
                </svg>
            </div>
            <b>Raging Developers</b><br><span class="h6 text-muted">Expense Management</span>
        </div>
        <div class="card li-item" style="--d:1">
            <div class="card-body login-card-body">
                <p class="text-center text-muted mb-3 li-item" style="--d:2">Sign in to continue</p>
                @if (session('success')) <div class="alert alert-success li-item" style="--d:2">{{ session('success') }}</div> @endif
                <div class="alert alert-danger" id="loginError" @unless($errors->any()) hidden @endunless role="alert">{{ $errors->first() }}</div>
                <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                    @csrf
                    <input type="hidden" name="remember" value="1">
                    <div class="input-group mb-3 li-item" style="--d:3">
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" autocomplete="username" required autofocus>
                        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
                    </div>
                    <div class="input-group mb-2 li-item" style="--d:4">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password" autocomplete="current-password" required>
                        <div class="input-group-append"><button type="button" class="input-group-text" id="togglePassword" aria-label="Show password"><span class="fas fa-eye"></span></button></div>
                    </div>
                    <div class="mb-3 text-muted small li-item" style="--d:5"><i class="fas fa-shield-alt mr-1"></i>You will stay signed in on this device.</div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg login-btn li-item" style="--d:6" id="loginBtn">
                        <span class="lb-text">Login</span>
                        <span class="lb-busy"><span class="spinner-border spinner-border-sm mr-2"></span>Signing in…</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="{{ asset('js/sfx.js') }}"></script>
<script src="{{ asset('js/theme.js') }}"></script>
<script src="{{ asset('js/login.js') }}"></script>
<script>
if ('serviceWorker' in navigator) { window.addEventListener('load', function () { navigator.serviceWorker.register('{{ asset('service-worker.js') }}'); }); }
</script>
</body>
</html>
