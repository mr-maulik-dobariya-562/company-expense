@extends('layouts.app')
@section('title', 'My Expenses')
@section('actions')<a href="{{ route('employee.expenses.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Expense</a>@endsection
@section('content')
<form class="mb-3" method="GET"><input type="month" name="month" value="{{ $month }}" class="form-control d-inline-block" style="max-width:180px"> <button class="btn btn-primary">Filter</button> <a href="{{ route('employee.expenses.index') }}" class="btn btn-secondary">Reset</a></form>
<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Title</th><th>Date</th><th>Month</th><th>Amount</th><th>Status</th><th width="180">Actions</th></tr></thead><tbody>
@forelse ($expenses as $expense)
<tr><td>{{ $expense->title }}</td><td>{{ $expense->expense_date->format('d M Y') }}</td><td>{{ $expense->month_date->format('M Y') }}</td><td>&#8377;{{ number_format($expense->amount, 2) }}</td><td><span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($expense->status) }}</span></td><td><a href="{{ route('employee.expenses.show', $expense) }}" class="btn btn-sm btn-secondary">View</a> <a href="{{ route('employee.expenses.edit', $expense) }}" class="btn btn-sm btn-info">Edit</a> <form method="POST" action="{{ route('employee.expenses.destroy', $expense) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense?')">Delete</button></form></td></tr>
@empty
<tr><td colspan="6" class="text-center">No expenses found.</td></tr>
@endforelse
</tbody></table></div><div class="card-footer">{{ $expenses->links() }}</div></div>

<div class="d-md-none">
@forelse ($expenses as $expense)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $expense->title }}</div><span class="label">{{ $expense->expense_date->format('d M Y') }} · {{ $expense->month_date->format('M Y') }}</span></div><div class="amount">&#8377;{{ number_format($expense->amount, 2) }}</div></div>
    <div class="mt-2"><span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($expense->status) }}</span></div>
    <div class="actions"><a href="{{ route('employee.expenses.show', $expense) }}" class="btn btn-secondary btn-sm">View</a><a href="{{ route('employee.expenses.edit', $expense) }}" class="btn btn-info btn-sm">Edit</a></div>
</div></div>
@empty
<div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div>
@endforelse
{{ $expenses->links() }}
</div>
@endsection
