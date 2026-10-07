<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>Login | Raging Developers Expense Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom-mobile.css') }}">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo"><b>Raging Developers</b><br><span class="h5">Expense Management</span></div>
    <div class="card">
        <div class="card-body login-card-body">
            @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <input type="hidden" name="remember" value="1">
                <div class="input-group mb-3">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required autofocus>
                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
                </div>
                <div class="row">
                    <div class="col-12 mb-2 text-muted small">You will stay signed in on this device.</div>
                    <div class="col-12"><button type="submit" class="btn btn-primary btn-block">Login</button></div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
if ('serviceWorker' in navigator) { window.addEventListener('load', function () { navigator.serviceWorker.register('/service-worker.js'); }); }
</script>
</body>
</html>
