@extends('layouts.app')
@section('title', 'Company Funds')
@section('subtitle', 'Money added to the company expense pool')
@section('actions')<a href="{{ route('admin.monthly-funds.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Add Fund</a>@endsection
@section('content')
@include('partials.month-filter', ['reset' => route('admin.monthly-funds.index')])

<div class="card d-none d-md-block"><div class="card-body table-responsive p-0"><table class="table mb-0"><thead><tr><th>Fund Date</th><th class="text-right">Amount</th><th>Note</th><th>Created By</th><th class="text-right">Actions</th></tr></thead><tbody>
@forelse ($funds as $fund)
<tr>
    <td class="font-weight-bold">{{ $fund->fund_date->format('d M Y') }}</td><td class="text-right amount text-success">₹{{ number_format($fund->amount, 2) }}</td><td class="text-muted">{{ $fund->note ?: '-' }}</td><td>{{ $fund->createdBy->name }}</td>
    <td><div class="td-actions justify-content-end">
        <a href="{{ route('admin.monthly-funds.edit', $fund) }}" class="btn btn-sm btn-info"><i class="fas fa-pen mr-1"></i>Edit</a>
        <form method="POST" action="{{ route('admin.monthly-funds.destroy', $fund) }}" onsubmit="return confirm('Delete this company fund?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash mr-1"></i>Delete</button></form>
    </div></td>
</tr>
@empty
<tr><td colspan="5"><div class="empty-state"><i class="fas fa-wallet"></i>No company funds found.</div></td></tr>
@endforelse
</tbody></table></div>
@if ($funds->hasPages())<div class="card-footer">{{ $funds->links() }}</div>@endif
</div>

<div class="d-md-none">
@forelse ($funds as $fund)
<div class="card mobile-record-card"><div class="card-body">
    <div class="d-flex justify-content-between"><div><div class="value">{{ $fund->fund_date->format('d M Y') }}</div><span class="label">Added by {{ $fund->createdBy->name }}</span></div><div class="amount text-success">₹{{ number_format($fund->amount, 2) }}</div></div>
    @if ($fund->note)<div class="mt-2 small text-muted">{{ $fund->note }}</div>@endif
    <div class="actions">
        <a href="{{ route('admin.monthly-funds.edit', $fund) }}" class="btn btn-info btn-sm">Edit</a>
        <form method="POST" action="{{ route('admin.monthly-funds.destroy', $fund) }}" onsubmit="return confirm('Delete this company fund?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete</button></form>
    </div>
</div></div>
@empty
<div class="card"><div class="empty-state"><i class="fas fa-wallet"></i>No company funds found.</div></div>
@endforelse
{{ $funds->links() }}
</div>
@endsection
