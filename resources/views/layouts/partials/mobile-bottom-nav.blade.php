@php
    $user = auth()->user();
    $isAdmin = $user?->role === 'admin';

    // Dock: two tabs, a raised centre action, one tab, then "More" (bottom sheet with everything + logout).
    if ($isAdmin) {
        $left = [['Home', 'admin.dashboard', 'fas fa-th-large'], ['Employees', 'admin.employees.index', 'fas fa-users']];
        $center = ['Settle', 'admin.settlements.index', 'fas fa-balance-scale', $isActive('admin.settlements.index')];
        $right = [['Expenses', 'admin.expenses.index', 'fas fa-receipt']];
    } else {
        $left = [['Home', 'employee.dashboard', 'fas fa-th-large'], ['Expenses', 'employee.expenses.index', 'fas fa-receipt']];
        $center = ['Add', 'employee.expenses.create', 'fas fa-plus', request()->routeIs('employee.expenses.create')];
        $right = [['Payments', 'employee.payments.index', 'fas fa-money-bill-wave']];
    }
    // When the centre action is the current page, don't also light up a tab
    $tabActive = fn ($route) => $isActive($route) && ! $center[3];
@endphp

<nav class="dock" id="dock" aria-label="Mobile navigation">
    <span class="dock-indicator" id="dockIndicator" aria-hidden="true"></span>
    @foreach ($left as [$label, $route, $icon])
        <a href="{{ route($route) }}" class="dock-item {{ $tabActive($route) ? 'active' : '' }}" @if($tabActive($route)) aria-current="page" @endif>
            <i class="{{ $icon }}"></i><span>{{ $label }}</span>
        </a>
    @endforeach

    <a href="{{ route($center[1]) }}" class="dock-center {{ $center[3] ? 'active' : '' }}" aria-label="{{ $isAdmin ? 'Settlements' : 'Add expense' }}" @if($center[3]) aria-current="page" @endif>
        <span class="dock-fab"><i class="{{ $center[2] }}"></i></span>
        <span class="dock-center-label">{{ $center[0] }}</span>
    </a>

    @foreach ($right as [$label, $route, $icon])
        <a href="{{ route($route) }}" class="dock-item {{ $tabActive($route) ? 'active' : '' }}" @if($tabActive($route)) aria-current="page" @endif>
            <i class="{{ $icon }}"></i><span>{{ $label }}</span>
        </a>
    @endforeach
    <button type="button" class="dock-item" data-sheet-open aria-controls="moreSheet" aria-expanded="false">
        <i class="fas fa-ellipsis-h"></i><span>More</span>
    </button>
</nav>

<div class="sheet-backdrop" id="sheetBackdrop" data-sheet-close hidden></div>
<section class="sheet" id="moreSheet" role="dialog" aria-modal="true" aria-label="More options" aria-hidden="true">
    <div class="sheet-grabber" data-sheet-drag><span></span></div>
    <div class="sheet-user">
        <span class="avatar">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
        <div><div class="font-weight-bold">{{ $user->name }}</div><div class="small text-muted">{{ $user->email }}</div></div>
    </div>
    <div class="sheet-grid">
        @foreach ($menu as [$label, $route, $icon])
            <a href="{{ route($route) }}" class="sheet-item {{ $isActive($route) ? 'active' : '' }}">
                <span class="sheet-icon"><i class="{{ $icon }}"></i></span><span>{{ $label }}</span>
            </a>
        @endforeach
    </div>
    <form method="POST" action="{{ route('logout') }}" data-logout>@csrf
        <button type="submit" class="btn btn-block sheet-logout"><i class="fas fa-sign-out-alt mr-2"></i>Logout</button>
    </form>
</section>
