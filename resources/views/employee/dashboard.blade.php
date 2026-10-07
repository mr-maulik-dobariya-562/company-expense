@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Hello, '.auth()->user()->name.' 👋')
@section('actions')<a href="{{ route('employee.expenses.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Add Expense</a>@endsection
@section('content')
@include('partials.stats', ['stats' => [
    ['Approved Expenses', '₹'.number_format($outstanding['total_expenses'], 2), 'warning', 'fas fa-receipt'],
    ['Total Paid', '₹'.number_format($outstanding['total_paid'], 2), 'success', 'fas fa-money-bill-wave'],
    ['Pending Receivable', '₹'.number_format($outstanding['pending_receivable'], 2), 'danger', 'fas fa-balance-scale'],
]])

@php $upi = auth()->user()->upi_id; @endphp
<div class="card upi-card mb-3"><div class="card-body d-flex align-items-center flex-wrap" style="gap:12px">
    <div class="upi-icon"><i class="fas fa-qrcode"></i></div>
    <div class="flex-grow-1" style="min-width:0">
        <div class="small text-muted font-weight-bold">Your UPI ID for reimbursements</div>
        @if ($upi)<div class="font-weight-bold text-break">{{ $upi }}</div>
        @else<div class="font-weight-bold text-danger">Not added yet</div>@endif
    </div>
    <button type="button" class="btn btn-{{ $upi ? 'secondary' : 'primary' }} btn-sm" data-toggle="modal" data-target="#upiModal"><i class="fas fa-{{ $upi ? 'pen' : 'plus' }} mr-1"></i>{{ $upi ? 'Change' : 'Add UPI' }}</button>
</div></div>

<div class="row d-none d-md-flex">
    <div class="col-lg-7"><div class="card">
        <div class="card-header d-flex align-items-center"><h3 class="card-title">Recent Expenses</h3><a href="{{ route('employee.expenses.index') }}" class="btn btn-secondary btn-sm ml-auto">View all</a></div>
        <div class="card-body table-responsive p-0">
            <table class="table mb-0"><thead><tr><th>Title</th><th>Date</th><th class="text-right">Amount</th><th>Status</th></tr></thead><tbody>
            @forelse ($recentExpenses as $expense)
                <tr><td class="font-weight-bold">{{ $expense->title }}</td><td>{{ $expense->expense_date->format('d M Y') }}</td><td class="text-right amount">₹{{ number_format($expense->amount, 2) }}</td><td>@include('partials.status-badge', ['status' => $expense->status])</td></tr>
            @empty
                <tr><td colspan="4"><div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div></td></tr>
            @endforelse
            </tbody></table>
        </div>
    </div></div>
    <div class="col-lg-5"><div class="card">
        <div class="card-header d-flex align-items-center"><h3 class="card-title">Recent Payments</h3><a href="{{ route('employee.payments.index') }}" class="btn btn-secondary btn-sm ml-auto">View all</a></div>
        <div class="card-body table-responsive p-0">
            <table class="table mb-0"><thead><tr><th>Date</th><th class="text-right">Amount</th><th>Note</th></tr></thead><tbody>
            @forelse ($recentPayments as $payment)
                <tr><td>{{ $payment->payment_date->format('d M Y') }}</td><td class="text-right amount text-success">₹{{ number_format($payment->amount, 2) }}</td><td class="text-muted">{{ $payment->note ?: '-' }}</td></tr>
            @empty
                <tr><td colspan="3"><div class="empty-state"><i class="fas fa-money-bill-wave"></i>No payments found.</div></td></tr>
            @endforelse
            </tbody></table>
        </div>
    </div></div>
</div>

<div class="d-md-none">
    <h2 class="section-title">Recent Expenses</h2>
    @forelse ($recentExpenses as $expense)
    <div class="card mobile-record-card"><div class="card-body"><div class="d-flex justify-content-between"><div><div class="value">{{ $expense->title }}</div><span class="label">{{ $expense->expense_date->format('d M Y') }}</span></div><div class="amount">₹{{ number_format($expense->amount, 2) }}</div></div><div class="mt-2">@include('partials.status-badge', ['status' => $expense->status])</div></div></div>
    @empty
    <div class="card"><div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div></div>
    @endforelse

    <h2 class="section-title mt-3">Recent Payments</h2>
    @forelse ($recentPayments as $payment)
    <div class="card mobile-record-card"><div class="card-body"><div class="d-flex justify-content-between"><div><div class="value">{{ $payment->payment_date->format('d M Y') }}</div><span class="label">{{ $payment->note ?: '-' }}</span></div><div class="amount text-success">₹{{ number_format($payment->amount, 2) }}</div></div></div></div>
    @empty
    <div class="card"><div class="empty-state"><i class="fas fa-money-bill-wave"></i>No payments found.</div></div>
    @endforelse
</div>

{{-- UPI popup: forced open (cannot be dismissed) while the employee has no UPI ID --}}
<div class="modal fade glass-modal" id="upiModal" tabindex="-1" aria-labelledby="upiModalTitle" aria-hidden="true" @unless($upi) data-backdrop="static" data-keyboard="false" @endunless>
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('employee.upi.update') }}">
            @csrf @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="upiModalTitle"><i class="fas fa-qrcode mr-2 text-primary"></i>{{ $upi ? 'Update your UPI ID' : 'Add your UPI ID' }}</h5>
                @if ($upi)<button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>@endif
            </div>
            <div class="modal-body">
                @unless ($upi)
                    <div class="alert alert-warning small mb-3"><i class="fas fa-exclamation-triangle mr-1"></i> Your UPI ID is required. The admin uses it to pay back your expenses by QR code.</div>
                @endunless
                <label for="upi_id_input">UPI ID</label>
                <input id="upi_id_input" name="upi_id" class="form-control @error('upi_id') is-invalid @enderror" value="{{ old('upi_id', $upi) }}" placeholder="e.g. 9876543210@ybl or name@okaxis" pattern="[a-zA-Z0-9._\-]{2,256}@[a-zA-Z][a-zA-Z0-9]{1,63}" title="Valid UPI ID like name@okaxis" autocapitalize="off" spellcheck="false" required>
                @error('upi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted d-block mt-2">Find it in Google Pay, PhonePe or Paytm under your profile.</small>
            </div>
            <div class="modal-footer">
                @if ($upi)<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>@endif
                <button class="btn btn-primary"><i class="fas fa-check mr-1"></i> Save UPI ID</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@if (! $upi || $errors->has('upi_id'))
<script>$(function () { $('#upiModal').modal('show'); });</script>
@endif
@endpush
