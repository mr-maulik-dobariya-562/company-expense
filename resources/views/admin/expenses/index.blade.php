@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<form class="card card-body mb-3" method="GET"><div class="row"><div class="col-md-3"><select name="employee_id" class="form-control"><option value="">All Employees</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? '') == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div><div class="col-md-3"><input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="form-control"></div><div class="col-md-3"><select name="status" class="form-control"><option value="">All Status</option>@foreach(['pending','approved','rejected'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div><div class="col-md-3"><button class="btn btn-primary">Filter</button> <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">Reset</a></div></div></form>
<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Employee</th><th>Title</th><th>Date</th><th>Amount</th><th>Status</th><th width="210">Actions</th></tr></thead><tbody>
@forelse ($expenses as $expense)
<tr><td>{{ $expense->user->name }}</td><td>{{ $expense->title }}</td><td>{{ $expense->expense_date->format('d M Y') }}</td><td>&#8377;{{ number_format($expense->amount, 2) }}</td><td><span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($expense->status) }}</span></td><td><a class="btn btn-sm btn-secondary" href="{{ route('admin.expenses.show', $expense) }}">View</a> <form method="POST" action="{{ route('admin.expenses.status', $expense) }}" class="d-inline">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-success">Approve</button></form> <form method="POST" action="{{ route('admin.expenses.status', $expense) }}" class="d-inline">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-danger">Reject</button></form></td></tr>
@empty
<tr><td colspan="6" class="text-center">No expenses found.</td></tr>
@endforelse
</tbody></table></div><div class="card-footer">{{ $expenses->links() }}</div></div>

<div class="d-md-none">
@forelse ($expenses as $expense)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $expense->title }}</div><span class="label">{{ $expense->user->name }} · {{ $expense->expense_date->format('d M Y') }}</span></div><div class="amount">&#8377;{{ number_format($expense->amount, 2) }}</div></div>
    <div class="mt-2"><span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($expense->status) }}</span></div>
    <div class="actions"><a class="btn btn-secondary btn-sm" href="{{ route('admin.expenses.show', $expense) }}">View</a><form method="POST" action="{{ route('admin.expenses.status', $expense) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="btn btn-success btn-sm">Approve</button></form><form method="POST" action="{{ route('admin.expenses.status', $expense) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="btn btn-danger btn-sm">Reject</button></form></div>
</div></div>
@empty
<div class="empty-state"><i class="fas fa-receipt"></i>No expenses found.</div>
@endforelse
{{ $expenses->links() }}
</div>
@endsection
