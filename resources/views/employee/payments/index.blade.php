@extends('layouts.app')
@section('title', 'My Payments')
@section('subtitle', 'Money the company has paid back to you')
@section('content')
@include('partials.month-filter', ['reset' => route('employee.payments.index')])

<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table mb-0"><thead><tr><th>Payment Date</th><th class="text-right">Amount</th><th>Paid By</th><th>Note</th></tr></thead><tbody>
@forelse ($payments as $payment)
<tr><td class="font-weight-bold">{{ $payment->payment_date->format('d M Y') }}</td><td class="text-right amount text-success">₹{{ number_format($payment->amount, 2) }}</td><td>{{ $payment->paidBy->name }}</td><td class="text-muted">{{ $payment->note ?: '-' }}</td></tr>
@empty
<tr><td colspan="4"><div class="empty-state"><i class="fas fa-money-bill-wave"></i>No payments found.</div></td></tr>
@endforelse
</tbody></table></div>
@if ($payments->hasPages())<div class="card-footer">{{ $payments->links() }}</div>@endif
</div>

<div class="d-md-none">
@forelse ($payments as $payment)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $payment->payment_date->format('d M Y') }}</div><span class="label">Paid by {{ $payment->paidBy->name }}</span></div><div class="amount text-success">₹{{ number_format($payment->amount, 2) }}</div></div>
    @if ($payment->note)<div class="mt-2 small text-muted">{{ $payment->note }}</div>@endif
</div></div>
@empty
<div class="card"><div class="empty-state"><i class="fas fa-money-bill-wave"></i>No payments found.</div></div>
@endforelse
{{ $payments->links() }}
</div>
@endsection
