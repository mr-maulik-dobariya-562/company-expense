@php
    $user = auth()->user();
    $isAdmin = $user?->role === 'admin';
    $menu = $isAdmin
        ? [
            ['Dashboard', 'admin.dashboard', 'fas fa-tachometer-alt'],
            ['Employees', 'admin.employees.index', 'fas fa-users'],
            ['Expenses', 'admin.expenses.index', 'fas fa-receipt'],
            ['Company Funds', 'admin.monthly-funds.index', 'fas fa-wallet'],
            ['Running Settlements', 'admin.settlements.index', 'fas fa-balance-scale'],
            ['Payments', 'admin.payments.index', 'fas fa-money-bill-wave'],
        ]
        : [
            ['Dashboard', 'employee.dashboard', 'fas fa-tachometer-alt'],
            ['My Expenses', 'employee.expenses.index', 'fas fa-receipt'],
            ['My Settlements', 'employee.settlements.index', 'fas fa-balance-scale'],
            ['My Payments', 'employee.payments.index', 'fas fa-money-bill-wave'],
        ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" href="{{ asset('icons/icon.svg') }}" type="image/svg+xml">
    <title>@yield('title', 'Expense Management') | Raging Developers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom-mobile.css') }}">
    <style>
        .brand-link .brand-text { font-weight: 700; }
        .content-wrapper { background: #f4f6f9; }
        .table td, .table th { vertical-align: middle; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><span class="nav-link">{{ $user->name }}</span></li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route($isAdmin ? 'admin.dashboard' : 'employee.dashboard') }}" class="brand-link">
            <span class="brand-text ml-2">Raging Developers</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column">
                    @foreach ($menu as [$label, $route, $icon])
                        <li class="nav-item">
                            <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) || request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.').'.*') ? 'active' : '' }}">
                                <i class="nav-icon {{ $icon }}"></i><p>{{ $label }}</p>
                            </a>
                        </li>
                    @endforeach
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="nav-link btn btn-link text-left w-100" type="submit"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <h1 class="m-0">@yield('title', 'Expense Management')</h1>
                @yield('actions')
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer"><strong>Raging Developers</strong> Expense Management</footer>
    @auth
        @include('layouts.partials.mobile-bottom-nav')
    @endauth
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () { navigator.serviceWorker.register('/service-worker.js'); });
}
</script>
</body>
</html>
