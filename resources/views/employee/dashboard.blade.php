@extends('layouts.app')
@section('title', 'Employee Dashboard')
@section('content')
<div class="row">
    @foreach ([
        ['My Total Approved Expenses', '&#8377;'.number_format($outstanding['total_expenses'], 2), 'bg-warning', 'fas fa-receipt'],
        ['My Total Paid Amount', '&#8377;'.number_format($outstanding['total_paid'], 2), 'bg-success', 'fas fa-money-bill-wave'],
        ['My Pending Receivable', '&#8377;'.number_format($outstanding['pending_receivable'], 2), 'bg-danger', 'fas fa-balance-scale'],
    ] as [$label, $value, $color, $icon])
    <div class="col-6 col-md-4"><div class="small-box {{ $color }}"><div class="inner"><h3>{!! $value !!}</h3><p>{{ $label }}</p></div><div class="icon"><i class="{{ $icon }}"></i></div></div></div>
    @endforeach
</div>
<div class="row d-none d-md-flex">
    <div class="col-lg-7"><div class="card">
        <div class="card-header d-flex justify-content-between"><h3 class="card-title">Recent Expenses</h3><a href="{{ route('employee.expenses.create') }}" class="btn btn-primary btn-sm ml-auto">Add Expense</a></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0"><thead><tr><th>Title</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>
            @forelse ($recentExpenses as $expense)
                <tr><td>{{ $expense->title }}</td><td>{{ $expense->expense_date->format('d M Y') }}</td><td>&#8377;{{ number_format($expense->amount, 2) }}</td><td><span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($expense->status) }}</span></td></tr>
            @empty
                <tr><td colspan="4" class="text-center">No expenses found.</td></tr>
            @endforelse
            </tbody></table>
        </div>
    </div></div>
    <div class="col-lg-5"><div class="card">
        <div class="card-header"><h3 class="card-title">Recent Payments</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0"><thead><tr><th>Date</th><th>Amount</th><th>Note</th></tr></thead><tbody>
            @forelse ($recentPayments as $payment)
                <tr><td>{{ $payment->payment_date->format('d M Y') }}</td><td>&#8377;{{ number_format($payment->amount, 2) }}</td><td>{{ $payment->note ?: '-' }}</td></tr>
            @empty
                <tr><td colspan="3" class="text-center">No payments found.</td></tr>
            @endforelse
            </tbody></table>
        </div>
    </div></div>
</div>

<div class="d-md-none">
    <div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h6 font-weight-bold mb-0">Recent Expenses</h2><a href="{{ route('employee.expenses.create') }}" class="btn btn-primary btn-sm">Add</a></div>
    @forelse ($recentExpenses as $expense)
    <div class="card mobile-record-card"><div class="card-body"><div class="d-flex justify-content-between"><div><div class="value">{{ $expense->title }}</div><span class="label">{{ $expense->expense_date->format('d M Y') }}</span></div><div class="amount">&#8377;{{ number_format($expense->amount, 2) }}</div></div><div class="mt-2"><span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($expense->status) }}</span></div></div></div>
    @empty
    <div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div>
    @endforelse

    <h2 class="h6 font-weight-bold mb-2 mt-3">Recent Payments</h2>
    @forelse ($recentPayments as $payment)
    <div class="card mobile-record-card"><div class="card-body"><div class="d-flex justify-content-between"><div><div class="value">{{ $payment->payment_date->format('d M Y') }}</div><span class="label">{{ $payment->note ?: '-' }}</span></div><div class="amount">&#8377;{{ number_format($payment->amount, 2) }}</div></div></div></div>
    @empty
    <div class="empty-state"><i class="fas fa-money-bill-wave"></i>No payments found.</div>
    @endforelse
</div>
@endsection
