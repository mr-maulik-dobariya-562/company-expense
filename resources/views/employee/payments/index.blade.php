@extends('layouts.app')
@section('title', 'My Payments')
@section('content')
<form class="mb-3" method="GET"><input type="month" name="month" value="{{ $month }}" class="form-control d-inline-block" style="max-width:180px"> <button class="btn btn-primary">Filter</button> <a href="{{ route('employee.payments.index') }}" class="btn btn-secondary">Reset</a></form>
<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Payment Date</th><th>Amount</th><th>Paid By</th><th>Note</th></tr></thead><tbody>
@forelse ($payments as $payment)
<tr><td>{{ $payment->payment_date->format('d M Y') }}</td><td>&#8377;{{ number_format($payment->amount, 2) }}</td><td>{{ $payment->paidBy->name }}</td><td>{{ $payment->note ?: '-' }}</td></tr>
@empty
<tr><td colspan="4" class="text-center">No payments found.</td></tr>
@endforelse
</tbody></table></div><div class="card-footer">{{ $payments->links() }}</div></div>

<div class="d-md-none">
@forelse ($payments as $payment)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $payment->payment_date->format('d M Y') }}</div><span class="label">Paid by {{ $payment->paidBy->name }}</span></div><div class="amount">&#8377;{{ number_format($payment->amount, 2) }}</div></div>
    <div class="mt-2"><span class="label">Note</span><div>{{ $payment->note ?: '-' }}</div></div>
</div></div>
@empty
<div class="empty-state"><i class="fas fa-money-bill-wave"></i>No payments found.</div>
@endforelse
{{ $payments->links() }}
</div>
@endsection
