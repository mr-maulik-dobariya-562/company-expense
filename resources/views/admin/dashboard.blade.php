@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Overview of company funds, expenses and settlements')
@section('content')
@include('partials.stats', ['stats' => [
    ['Total Employees', $totalEmployees, 'primary', 'fas fa-users'],
    ['Active Employees', $activeEmployees, 'success', 'fas fa-user-check'],
    ['Approved Expenses', '₹'.number_format($totalApprovedExpenses, 2), 'warning', 'fas fa-receipt'],
    ['Company Funds', '₹'.number_format($totalCompanyFunds, 2), 'info', 'fas fa-wallet'],
    ['Paid to Employees', '₹'.number_format($totalPaidToEmployees, 2), 'success', 'fas fa-money-bill-wave'],
    ['Available Balance', '₹'.number_format($availableCompanyBalance, 2), 'primary', 'fas fa-piggy-bank'],
    ['Pending Payable', '₹'.number_format($totalPendingEmployeePayable, 2), 'danger', 'fas fa-balance-scale'],
]])

<div class="card d-none d-md-block">
    <div class="card-header d-flex align-items-center"><h3 class="card-title">Recent Expenses</h3><a href="{{ route('admin.expenses.index') }}" class="btn btn-sm btn-secondary ml-auto">View all</a></div>
    <div class="card-body table-responsive p-0">
        <table class="table mb-0"><thead><tr><th>Employee</th><th>Title</th><th>Date</th><th class="text-right">Amount</th><th>Status</th></tr></thead><tbody>
        @forelse ($recentExpenses as $expense)
            <tr><td class="font-weight-bold">{{ $expense->user->name }}</td><td>{{ $expense->title }}</td><td>{{ $expense->expense_date->format('d M Y') }}</td><td class="text-right amount">₹{{ number_format($expense->amount, 2) }}</td><td>@include('partials.status-badge', ['status' => $expense->status])</td></tr>
        @empty
            <tr><td colspan="5"><div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div></td></tr>
        @endforelse
        </tbody></table>
    </div>
</div>

<div class="d-md-none">
    <div class="d-flex justify-content-between align-items-center"><h2 class="section-title">Recent Expenses</h2><a href="{{ route('admin.expenses.index') }}" class="small font-weight-bold">View all</a></div>
    @forelse ($recentExpenses as $expense)
    <div class="card mobile-record-card"><div class="card-body">
        <div class="d-flex justify-content-between"><div><div class="value">{{ $expense->title }}</div><span class="label">{{ $expense->user->name }} · {{ $expense->expense_date->format('d M Y') }}</span></div><div class="amount">₹{{ number_format($expense->amount, 2) }}</div></div>
        <div class="mt-2">@include('partials.status-badge', ['status' => $expense->status])</div>
    </div></div>
    @empty
    <div class="card"><div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div></div>
    @endforelse
</div>
@endsection
