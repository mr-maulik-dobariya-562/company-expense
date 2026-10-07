@extends('layouts.app')
@section('title', 'My Expenses')
@section('subtitle', 'Track everything you have spent for the company')
@section('actions')<a href="{{ route('employee.expenses.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Add Expense</a>@endsection
@section('content')
@include('partials.month-filter', ['reset' => route('employee.expenses.index')])

<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table mb-0"><thead><tr><th>Title</th><th>Date</th><th>Month</th><th class="text-right">Amount</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
@forelse ($expenses as $expense)
<tr>
    <td class="font-weight-bold">{{ $expense->title }}</td><td>{{ $expense->expense_date->format('d M Y') }}</td><td>{{ $expense->month_date->format('M Y') }}</td>
    <td class="text-right amount">₹{{ number_format($expense->amount, 2) }}</td>
    <td>@include('partials.status-badge', ['status' => $expense->status])</td>
    <td><div class="td-actions justify-content-end">
        <a href="{{ route('employee.expenses.show', $expense) }}" class="btn btn-sm btn-secondary" title="View"><i class="fas fa-eye"></i></a>
        <a href="{{ route('employee.expenses.edit', $expense) }}" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-pen"></i></a>
        <form method="POST" action="{{ route('employee.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button></form>
    </div></td>
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
    <div class="d-flex justify-content-between"><div><div class="value">{{ $expense->title }}</div><span class="label">{{ $expense->expense_date->format('d M Y') }}</span></div><div class="amount">₹{{ number_format($expense->amount, 2) }}</div></div>
    <div class="mt-2">@include('partials.status-badge', ['status' => $expense->status])</div>
    <div class="actions">
        <a href="{{ route('employee.expenses.show', $expense) }}" class="btn btn-secondary btn-sm">View</a>
        <a href="{{ route('employee.expenses.edit', $expense) }}" class="btn btn-info btn-sm">Edit</a>
        <form method="POST" action="{{ route('employee.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete</button></form>
    </div>
</div></div>
@empty
<div class="card"><div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.<div class="mt-3"><a href="{{ route('employee.expenses.create') }}" class="btn btn-primary btn-sm">Add your first expense</a></div></div></div>
@endforelse
{{ $expenses->links() }}
</div>
@endsection
