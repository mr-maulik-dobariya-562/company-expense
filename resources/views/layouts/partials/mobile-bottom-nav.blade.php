@php
    $user = auth()->user();
    $isAdmin = $user?->role === 'admin';
    $items = $isAdmin
        ? [
            ['Dashboard', 'admin.dashboard', 'fas fa-home'],
            ['Employees', 'admin.employees.index', 'fas fa-users'],
            ['Expenses', 'admin.expenses.index', 'fas fa-receipt'],
            ['Settlements', 'admin.settlements.index', 'fas fa-balance-scale'],
        ]
        : [
            ['Dashboard', 'employee.dashboard', 'fas fa-home'],
            ['Expenses', 'employee.expenses.index', 'fas fa-receipt'],
            ['Settlement', 'employee.settlements.index', 'fas fa-balance-scale'],
            ['Payments', 'employee.payments.index', 'fas fa-money-bill-wave'],
        ];
@endphp

<nav class="mobile-bottom-nav d-md-none" aria-label="Mobile navigation">
    @foreach ($items as [$label, $route, $icon])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($route) || request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.').'.*') ? 'active' : '' }}">
            <i class="{{ $icon }}"></i>
            <span>{{ $label }}</span>
        </a>
    @endforeach
    @if ($isAdmin)
        <a href="#" data-widget="pushmenu">
            <i class="fas fa-ellipsis-h"></i>
            <span>More</span>
        </a>
    @endif
</nav>
