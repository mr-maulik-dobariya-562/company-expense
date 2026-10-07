@extends('layouts.app')
@section('title', 'Employees')
@section('subtitle', 'Manage team members and their access')
@section('actions')<a href="{{ route('admin.employees.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Add Employee</a>@endsection
@section('content')
<div class="card d-none d-md-block"><div class="card-body table-responsive p-0">
<table class="table mb-0"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>UPI ID</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
@forelse ($employees as $employee)
<tr>
    <td class="font-weight-bold">{{ $employee->name }}</td><td>{{ $employee->email }}</td><td>{{ $employee->phone ?: '-' }}</td>
    <td>@if ($employee->upi_id)<code>{{ $employee->upi_id }}</code>@else<span class="badge badge-warning">Not added</span>@endif</td>
    <td>@include('partials.status-badge', ['status' => $employee->status])</td>
    <td><div class="td-actions justify-content-end">
        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-info"><i class="fas fa-pen mr-1"></i>Edit</a>
        @if ($employee->status === 'active')
            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" onsubmit="return confirm('Mark this employee as inactive?')">@csrf @method('DELETE')<button class="btn btn-sm btn-warning"><i class="fas fa-user-slash mr-1"></i>Deactivate</button></form>
        @endif
    </div></td>
</tr>
@empty
<tr><td colspan="6"><div class="empty-state"><i class="fas fa-users"></i>No employees found.</div></td></tr>
@endforelse
</tbody></table></div>
@if ($employees->hasPages())<div class="card-footer">{{ $employees->links() }}</div>@endif
</div>

<div class="d-md-none">
@forelse ($employees as $employee)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start"><div><div class="value">{{ $employee->name }}</div><span class="label">{{ $employee->email }}</span><span class="label">{{ $employee->phone ?: 'No phone' }}</span><span class="label">UPI: {{ $employee->upi_id ?: 'Not added' }}</span></div>@include('partials.status-badge', ['status' => $employee->status])</div>
    <div class="actions">
        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-info btn-sm">Edit</a>
        @if ($employee->status === 'active')
            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" onsubmit="return confirm('Mark this employee as inactive?')">@csrf @method('DELETE')<button class="btn btn-warning btn-sm">Deactivate</button></form>
        @endif
    </div>
</div></div>
@empty
<div class="card"><div class="empty-state"><i class="fas fa-users"></i>No employees found.</div></div>
@endforelse
{{ $employees->links() }}
</div>
@endsection
