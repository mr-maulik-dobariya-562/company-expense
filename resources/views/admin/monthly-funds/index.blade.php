@extends('layouts.app')
@section('title', 'Company Funds')
@section('actions')<a href="{{ route('admin.monthly-funds.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Fund</a>@endsection
@section('content')
<form class="mb-3" method="GET"><input type="month" name="month" value="{{ $month }}" class="form-control d-inline-block" style="max-width:180px"> <button class="btn btn-primary">Filter</button> <a href="{{ route('admin.monthly-funds.index') }}" class="btn btn-secondary">Reset</a></form>
<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Fund Date</th><th>Amount</th><th>Note</th><th>Created By</th><th width="150">Actions</th></tr></thead><tbody>
@forelse ($funds as $fund)
<tr><td>{{ $fund->fund_date->format('d M Y') }}</td><td>&#8377;{{ number_format($fund->amount, 2) }}</td><td>{{ $fund->note ?: '-' }}</td><td>{{ $fund->createdBy->name }}</td><td><a href="{{ route('admin.monthly-funds.edit', $fund) }}" class="btn btn-sm btn-info">Edit</a> <form method="POST" action="{{ route('admin.monthly-funds.destroy', $fund) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete this company fund?')">Delete</button></form></td></tr>
@empty
<tr><td colspan="5" class="text-center">No company funds found.</td></tr>
@endforelse
</tbody></table></div><div class="card-footer">{{ $funds->links() }}</div></div>

<div class="d-md-none">
@forelse ($funds as $fund)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $fund->fund_date->format('d M Y') }}</div><span class="label">Created by {{ $fund->createdBy->name }}</span></div><div class="amount">&#8377;{{ number_format($fund->amount, 2) }}</div></div>
    <div class="mt-2"><span class="label">Note</span><div>{{ $fund->note ?: '-' }}</div></div>
    <div class="actions"><a href="{{ route('admin.monthly-funds.edit', $fund) }}" class="btn btn-info btn-sm">Edit</a><form method="POST" action="{{ route('admin.monthly-funds.destroy', $fund) }}">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" onclick="return confirm('Delete this company fund?')">Delete</button></form></div>
</div></div>
@empty
<div class="empty-state"><i class="fas fa-wallet"></i>No company funds found.</div>
@endforelse
{{ $funds->links() }}
</div>
@endsection
