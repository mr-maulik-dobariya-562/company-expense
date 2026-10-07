<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f46e5">
    <title>Offline | Raging Developers Expense Management</title>
    @include('partials.theme-head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/glass.css') }}">
</head>
<body class="login-page">
<div class="login-box">
    <div class="card">
        <div class="card-body text-center p-4">
            <i class="fas fa-wifi text-muted mb-3" style="font-size:42px"></i>
            <h1 class="h4 font-weight-bold">You are offline</h1>
            <p class="text-muted">Please check your internet connection.</p>
            <button class="btn btn-primary" onclick="location.reload()"><i class="fas fa-redo mr-1"></i> Try again</button>
        </div>
    </div>
</div>
</body>
</html>
