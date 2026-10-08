@php
    $user = auth()->user();
    $isAdmin = $user?->role === 'admin';
    $menu = $isAdmin
        ? [
            ['Dashboard', 'admin.dashboard', 'fas fa-th-large'],
            ['Employees', 'admin.employees.index', 'fas fa-users'],
            ['Expenses', 'admin.expenses.index', 'fas fa-receipt'],
            ['Company Funds', 'admin.monthly-funds.index', 'fas fa-wallet'],
            ['Settlements', 'admin.settlements.index', 'fas fa-balance-scale'],
            ['Payments', 'admin.payments.index', 'fas fa-money-bill-wave'],
        ]
        : [
            ['Dashboard', 'employee.dashboard', 'fas fa-th-large'],
            ['My Expenses', 'employee.expenses.index', 'fas fa-receipt'],
            ['My Settlement', 'employee.settlements.index', 'fas fa-balance-scale'],
            ['My Payments', 'employee.payments.index', 'fas fa-money-bill-wave'],
        ];

    // "x.index" routes also own their create/edit/show pages; other routes match exactly.
    $isActive = function (string $route): bool {
        if (str_ends_with($route, '.index')) {
            return request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.').'.*');
        }
        return request()->routeIs($route);
    };
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" href="{{ asset('icons/icon.svg') }}" type="image/svg+xml">
    <title>@yield('title', 'Expense Management') | Raging Developers</title>
    @include('partials.theme-head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/glass.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shell.css') }}">
    <link rel="stylesheet" href="{{ asset('css/motion.css') }}">
</head>
<body class="app-body">

{{-- Desktop sidebar --}}
<aside class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="sb-head">
        <button type="button" class="sb-brand" data-sidebar-expand title="Raging Developers">
            <span class="brand-mark">R</span>
            <span class="sb-text sb-brand-text">Raging Developers</span>
        </button>
        <button type="button" class="sb-collapse" data-sidebar-toggle aria-label="Collapse sidebar" title="Collapse sidebar">
            <i class="fas fa-angle-double-left"></i>
        </button>
    </div>
    <nav class="sb-nav">
        @foreach ($menu as [$label, $route, $icon])
            <a href="{{ route($route) }}" class="sb-link {{ $isActive($route) ? 'active' : '' }}" @if($isActive($route)) aria-current="page" @endif data-tip="{{ $label }}">
                <i class="{{ $icon }}"></i><span class="sb-text">{{ $label }}</span>
            </a>
        @endforeach
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="sb-foot" data-logout>@csrf
        <button type="submit" class="sb-link sb-logout" data-tip="Logout"><i class="fas fa-sign-out-alt"></i><span class="sb-text">Logout</span></button>
    </form>
</aside>

<div class="app-main">
    <header class="topbar">
        <a href="{{ route($isAdmin ? 'admin.dashboard' : 'employee.dashboard') }}" class="topbar-brand"><span class="brand-mark">R</span></a>
        <div class="topbar-right">
            <div class="hstats" id="headerStats" data-url="{{ route('header-stats') }}" aria-live="polite" aria-busy="true">
                @foreach (range(1, $isAdmin ? 2 : 1) as $i)
                    <span class="hstat is-loading" aria-hidden="true"><span class="hstat-icon"></span><span class="hstat-body"><span class="sk sk-label"></span><span class="sk sk-value"></span></span></span>
                @endforeach
            </div>
            @include('partials.theme-toggle')
            <span class="user-chip">
                <span class="avatar">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <span class="name">{{ $user->name }}</span>
                <span class="role">· {{ $isAdmin ? 'Admin' : 'Employee' }}</span>
            </span>
        </div>
    </header>

    <main class="app-content">
        <div class="page-head">
            <div>
                <h1>@yield('title', 'Expense Management')</h1>
                @hasSection('subtitle')<p class="page-sub">@yield('subtitle')</p>@endif
            </div>
            @hasSection('actions')<div class="page-actions">@yield('actions')</div>@endif
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle mr-1"></i>
                @if ($errors->count() === 1) {{ $errors->first() }}
                @else <ul class="mb-0 pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                @endif
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="app-footer"><strong>Raging Developers</strong> · Expense Management</footer>
</div>

@include('layouts.partials.mobile-bottom-nav', ['menu' => $menu, 'isActive' => $isActive])

{{-- Logout confirmation (opened by any form[data-logout]) --}}
<div class="modal fade glass-modal logout-modal" id="logoutModal" tabindex="-1" aria-labelledby="logoutTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="lo-body">
                <div class="lo-icon" aria-hidden="true"><i class="fas fa-sign-out-alt"></i></div>
                <h5 class="lo-title" id="logoutTitle">Log out?</h5>
                <p class="lo-text">You'll need to sign in again to use your account on this device.</p>
                <div class="lo-user">
                    <span class="avatar">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    <div class="min-w-0"><div class="font-weight-bold text-truncate">{{ $user->name }}</div><div class="small text-muted text-truncate">{{ $user->email }}</div></div>
                </div>
            </div>
            <div class="lo-actions">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="logoutConfirm"><i class="fas fa-sign-out-alt mr-1"></i> Log out</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/sfx.js') }}"></script>
<script src="{{ asset('js/theme.js') }}"></script>
<script src="{{ asset('js/shell.js') }}"></script>
<script>
// Prevent double-submits on every form
document.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return;
    e.target.querySelectorAll('button[type=submit], button:not([type])').forEach(function (b) {
        setTimeout(function () { b.disabled = true; }, 0);
    });
});
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () { navigator.serviceWorker.register('{{ asset('service-worker.js') }}'); });
}
</script>
@stack('scripts')
</body>
</html>
