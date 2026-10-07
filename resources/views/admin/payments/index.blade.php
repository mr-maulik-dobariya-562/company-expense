@extends('layouts.app')
@section('title', 'Payments')
@section('content')
<form class="card card-body mb-3" method="GET"><div class="row">
    <div class="col-md-3"><select name="employee_id" class="form-control"><option value="">All Employees</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? '') == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control"></div>
    <div class="col-md-2"><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control"></div>
    <div class="col-md-2"><input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="form-control"></div>
    <div class="col-md-3"><button class="btn btn-primary">Filter</button> <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">Reset</a></div>
</div></form>
<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Employee</th><th>Amount</th><th>Payment Date</th><th>Paid By</th><th>Note</th></tr></thead><tbody>
@forelse ($payments as $payment)
<tr><td>{{ $payment->user->name }}</td><td>&#8377;{{ number_format($payment->amount, 2) }}</td><td>{{ $payment->payment_date->format('d M Y') }}</td><td>{{ $payment->paidBy->name }}</td><td>{{ $payment->note ?: '-' }}</td></tr>
@empty
<tr><td colspan="5" class="text-center">No payments found.</td></tr>
@endforelse
</tbody></table></div><div class="card-footer">{{ $payments->links() }}</div></div>

<div class="d-md-none">
@forelse ($payments as $payment)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $payment->user->name }}</div><span class="label">{{ $payment->payment_date->format('d M Y') }} · Paid by {{ $payment->paidBy->name }}</span></div><div class="amount">&#8377;{{ number_format($payment->amount, 2) }}</div></div>
    <div class="mt-2"><span class="label">Note</span><div>{{ $payment->note ?: '-' }}</div></div>
</div></div>
@empty
<div class="empty-state"><i class="fas fa-money-bill-wave"></i>No payments found.</div>
@endforelse
{{ $payments->links() }}
</div>
@endsection
