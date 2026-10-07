@extends('layouts.app')
@section('title', 'Employees')
@section('actions')<a href="{{ route('admin.employees.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Employee</a>@endsection
@section('content')
<div class="card d-none d-md-block"><div class="card-body table-responsive p-0">
<table class="table table-striped mb-0"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th width="170">Actions</th></tr></thead><tbody>
@forelse ($employees as $employee)
<tr><td>{{ $employee->name }}</td><td>{{ $employee->email }}</td><td>{{ $employee->phone ?: '-' }}</td><td><span class="badge badge-{{ $employee->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($employee->status) }}</span></td><td><a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-info">Edit</a> <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-warning" onclick="return confirm('Mark this employee inactive?')">Inactive</button></form></td></tr>
@empty
<tr><td colspan="5" class="text-center">No employees found.</td></tr>
@endforelse
</tbody></table></div><div class="card-footer">{{ $employees->links() }}</div></div>

<div class="d-md-none">
@forelse ($employees as $employee)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start"><div><div class="value">{{ $employee->name }}</div><span class="label">{{ $employee->email }}</span></div><span class="badge badge-{{ $employee->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($employee->status) }}</span></div>
    <div class="mt-2"><span class="label">Phone</span><div class="value">{{ $employee->phone ?: '-' }}</div></div>
    <div class="actions"><a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-info btn-sm">Edit</a><form method="POST" action="{{ route('admin.employees.destroy', $employee) }}">@csrf @method('DELETE')<button class="btn btn-warning btn-sm" onclick="return confirm('Mark this employee inactive?')">Inactive</button></form></div>
</div></div>
@empty
<div class="empty-state"><i class="fas fa-users"></i>No employees found.</div>
@endforelse
{{ $employees->links() }}
</div>
@endsection
