@extends('layouts.app')
@section('title', 'My Settlement')
@section('subtitle', 'How much the company owes you')
@section('content')
@include('partials.stats', ['stats' => [
    ['Approved Expenses', '₹'.number_format($outstanding['total_expenses'], 2), 'warning', 'fas fa-receipt'],
    ['Total Paid', '₹'.number_format($outstanding['total_paid'], 2), 'success', 'fas fa-money-bill-wave'],
    ['Pending Receivable', '₹'.number_format($outstanding['pending_receivable'], 2), 'danger', 'fas fa-balance-scale'],
]])

<div class="card">
    <div class="card-header"><h3 class="card-title">Month-wise Expense Report</h3></div>
    <div class="card-body table-responsive p-0"><table class="table mb-0"><thead><tr><th>Month</th><th class="text-right">Total Approved Expenses</th></tr></thead><tbody>
    @forelse ($monthlyExpenses as $row)
        <tr><td class="font-weight-bold">{{ $row->month_date->format('M Y') }}</td><td class="text-right amount">₹{{ number_format($row->total_expenses, 2) }}</td></tr>
    @empty
        <tr><td colspan="2"><div class="empty-state"><i class="fas fa-receipt"></i>No approved expenses found.</div></td></tr>
    @endforelse
    </tbody></table></div>
</div>
@endsection
