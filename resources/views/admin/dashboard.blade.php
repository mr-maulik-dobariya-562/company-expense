@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="row">
    @foreach ([
        ['Total Employees', $totalEmployees, 'bg-info', 'fas fa-users'],
        ['Active Employees', $activeEmployees, 'bg-success', 'fas fa-user-check'],
        ['Total Approved Expenses', '&#8377;'.number_format($totalApprovedExpenses, 2), 'bg-warning', 'fas fa-receipt'],
        ['Total Company Funds', '&#8377;'.number_format($totalCompanyFunds, 2), 'bg-primary', 'fas fa-wallet'],
        ['Total Paid to Employees', '&#8377;'.number_format($totalPaidToEmployees, 2), 'bg-success', 'fas fa-money-bill-wave'],
        ['Available Company Balance', '&#8377;'.number_format($availableCompanyBalance, 2), 'bg-info', 'fas fa-piggy-bank'],
        ['Total Pending Employee Payable', '&#8377;'.number_format($totalPendingEmployeePayable, 2), 'bg-danger', 'fas fa-balance-scale'],
    ] as [$label, $value, $color, $icon])
    <div class="col-6 col-md-4 col-lg-3"><div class="small-box {{ $color }}"><div class="inner"><h4>{!! $value !!}</h4><p>{{ $label }}</p></div><div class="icon"><i class="{{ $icon }}"></i></div></div></div>
    @endforeach
</div>
<div class="card d-none d-md-block">
    <div class="card-header"><h3 class="card-title">Recent Expenses</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-striped mb-0"><thead><tr><th>Employee</th><th>Title</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>
        @forelse ($recentExpenses as $expense)
            <tr><td>{{ $expense->user->name }}</td><td>{{ $expense->title }}</td><td>{{ $expense->expense_date->format('d M Y') }}</td><td>&#8377;{{ number_format($expense->amount, 2) }}</td><td><span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($expense->status) }}</span></td></tr>
        @empty
            <tr><td colspan="5" class="text-center">No expenses found.</td></tr>
        @endforelse
        </tbody></table>
    </div>
</div>

<div class="d-md-none">
<h2 class="h6 font-weight-bold mb-2">Recent Expenses</h2>
@forelse ($recentExpenses as $expense)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $expense->title }}</div><span class="label">{{ $expense->user->name }} · {{ $expense->expense_date->format('d M Y') }}</span></div><div class="amount">&#8377;{{ number_format($expense->amount, 2) }}</div></div>
    <div class="mt-2"><span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($expense->status) }}</span></div>
</div></div>
@empty
<div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div>
@endforelse
</div>
@endsection
