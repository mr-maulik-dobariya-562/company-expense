@extends('layouts.app')
@section('title', 'Running Settlements')
@section('subtitle', 'Pay employees what they are owed for approved expenses')
@section('content')
@include('partials.stats', ['stats' => [
    ['Company Funds', '₹'.number_format($summary['total_funds'], 2), 'primary', 'fas fa-wallet'],
    ['Paid to Employees', '₹'.number_format($summary['total_paid'], 2), 'success', 'fas fa-money-bill-wave'],
    ['Available Balance', '₹'.number_format($summary['available_balance'], 2), 'info', 'fas fa-piggy-bank'],
    ['Pending Payable', '₹'.number_format($pendingPayable, 2), 'danger', 'fas fa-balance-scale'],
]])

<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table mb-0"><thead><tr><th>Employee</th><th class="text-right">Approved Expenses</th><th class="text-right">Paid</th><th class="text-right">Pending</th><th>Status</th><th class="text-right">Action</th></tr></thead><tbody>
@forelse ($rows as $row)
@php $pending = $row['pending_receivable'] > 0; @endphp
<tr>
    <td class="font-weight-bold">{{ $row['name'] }}</td>
    <td class="text-right">₹{{ number_format($row['total_expenses'], 2) }}</td>
    <td class="text-right text-success">₹{{ number_format($row['total_paid'], 2) }}</td>
    <td class="text-right amount {{ $pending ? 'text-danger' : '' }}">₹{{ number_format($row['pending_receivable'], 2) }}</td>
    <td><span class="badge badge-{{ $pending ? 'warning' : 'success' }}">{{ $row['payment_status'] }}</span></td>
    <td>
        @if ($pending)
        <div class="td-actions justify-content-end">@include('admin.settlements._pay-button', ['size' => 'btn-sm'])</div>
        @else
        <div class="text-right text-muted small"><i class="fas fa-check-circle text-success mr-1"></i>Settled</div>
        @endif
    </td>
</tr>
@empty
<tr><td colspan="6"><div class="empty-state"><i class="fas fa-balance-scale"></i>No employees to settle.</div></td></tr>
@endforelse
</tbody></table></div></div>

<div class="d-md-none">
@forelse ($rows as $row)
@php $pending = $row['pending_receivable'] > 0; @endphp
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start"><div class="value">{{ $row['name'] }}</div><span class="badge badge-{{ $pending ? 'warning' : 'success' }}">{{ $row['payment_status'] }}</span></div>
    <div class="row mt-2">
        <div class="col-6 mb-2"><span class="label">Expenses</span><div class="amount">₹{{ number_format($row['total_expenses'], 2) }}</div></div>
        <div class="col-6 mb-2"><span class="label">Paid</span><div class="amount text-success">₹{{ number_format($row['total_paid'], 2) }}</div></div>
        <div class="col-12"><span class="label">Pending</span><div class="amount {{ $pending ? 'text-danger' : '' }}" style="font-size:18px">₹{{ number_format($row['pending_receivable'], 2) }}</div></div>
    </div>
    @if ($pending)
    <div class="mt-3">@include('admin.settlements._pay-button', ['size' => 'btn-block'])</div>
    @endif
</div></div>
@empty
<div class="card"><div class="empty-state"><i class="fas fa-balance-scale"></i>No employees to settle.</div></div>
@endforelse
</div>

@include('admin.settlements._pay-modal')
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="{{ asset('js/upi-pay.js') }}"></script>
@endpush
