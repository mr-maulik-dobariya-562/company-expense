@extends('layouts.app')
@section('title', 'Expenses')
@section('subtitle', 'Review and approve employee expenses')
@section('content')
<form class="card card-body mb-3" method="GET">
    <div class="filter-bar">
        <div><label class="mb-1">Employee</label><select name="employee_id" class="form-control"><option value="">All Employees</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? '') == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div>
        <div><label class="mb-1">Month</label><input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="form-control"></div>
        <div><label class="mb-1">Status</label><select name="status" class="form-control"><option value="">All Status</option>@foreach(['pending','approved','rejected'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="filter-buttons"><button class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button><a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">Reset</a></div>
    </div>
</form>

<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table mb-0"><thead><tr><th>Employee</th><th>Title</th><th>Date</th><th class="text-right">Amount</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
@forelse ($expenses as $expense)
<tr>
    <td class="font-weight-bold">{{ $expense->user->name }}</td><td>{{ $expense->title }}</td><td>{{ $expense->expense_date->format('d M Y') }}</td>
    <td class="text-right amount">₹{{ number_format($expense->amount, 2) }}</td>
    <td>@include('partials.status-badge', ['status' => $expense->status])</td>
    <td><div class="td-actions justify-content-end"><a class="btn btn-sm btn-secondary" href="{{ route('admin.expenses.show', $expense) }}" title="View"><i class="fas fa-eye"></i></a>@include('partials.expense-status-actions')</div></td>
</tr>
@empty
<tr><td colspan="6"><div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div></td></tr>
@endforelse
</tbody></table></div>
@if ($expenses->hasPages())<div class="card-footer">{{ $expenses->links() }}</div>@endif
</div>

<div class="d-md-none">
@forelse ($expenses as $expense)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $expense->title }}</div><span class="label">{{ $expense->user->name }} · {{ $expense->expense_date->format('d M Y') }}</span></div><div class="amount">₹{{ number_format($expense->amount, 2) }}</div></div>
    <div class="mt-2">@include('partials.status-badge', ['status' => $expense->status])</div>
    <div class="actions"><a class="btn btn-secondary btn-sm" href="{{ route('admin.expenses.show', $expense) }}">View</a>@include('partials.expense-status-actions')</div>
</div></div>
@empty
<div class="card"><div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div></div>
@endforelse
{{ $expenses->links() }}
</div>
@endsection
