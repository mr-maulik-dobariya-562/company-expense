@extends('layouts.app')
@section('title', 'Payments')
@section('subtitle', 'Settlement payments made to employees')
@section('content')
<form class="card card-body mb-3" method="GET">
    <div class="filter-bar">
        <div><label class="mb-1">Employee</label><select name="employee_id" class="form-control"><option value="">All Employees</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? '') == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div>
        <div><label class="mb-1">From</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control"></div>
        <div><label class="mb-1">To</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control"></div>
        <div><label class="mb-1">Month</label><input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="form-control"></div>
        <div class="filter-buttons"><button class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button><a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">Reset</a></div>
    </div>
</form>

<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table mb-0"><thead><tr><th>Employee</th><th class="text-right">Amount</th><th>Payment Date</th><th>Paid By</th><th>Note</th></tr></thead><tbody>
@forelse ($payments as $payment)
<tr><td class="font-weight-bold">{{ $payment->user->name }}</td><td class="text-right amount text-success">₹{{ number_format($payment->amount, 2) }}</td><td>{{ $payment->payment_date->format('d M Y') }}</td><td>{{ $payment->paidBy->name }}</td><td class="text-muted">{{ $payment->note ?: '-' }}</td></tr>
@empty
<tr><td colspan="5"><div class="empty-state"><i class="fas fa-money-bill-wave"></i>No payments found.</div></td></tr>
@endforelse
</tbody></table></div>
@if ($payments->hasPages())<div class="card-footer">{{ $payments->links() }}</div>@endif
</div>

<div class="d-md-none">
@forelse ($payments as $payment)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $payment->user->name }}</div><span class="label">{{ $payment->payment_date->format('d M Y') }} · by {{ $payment->paidBy->name }}</span></div><div class="amount text-success">₹{{ number_format($payment->amount, 2) }}</div></div>
    @if ($payment->note)<div class="mt-2 small text-muted">{{ $payment->note }}</div>@endif
</div></div>
@empty
<div class="card"><div class="empty-state"><i class="fas fa-money-bill-wave"></i>No payments found.</div></div>
@endforelse
{{ $payments->links() }}
</div>
@endsection
