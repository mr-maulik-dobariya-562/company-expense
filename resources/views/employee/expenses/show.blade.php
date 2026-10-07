@extends('layouts.app')
@section('title', 'Expense Detail')
@section('actions')<a href="{{ route('employee.expenses.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Back</a>@endsection
@section('content')
<div class="card"><div class="card-body p-md-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap mb-3" style="gap:10px">
        <h2 class="h4 font-weight-bold mb-0">{{ $expense->title }}</h2>
        <div class="text-md-right"><div class="h3 font-weight-bold mb-1">₹{{ number_format($expense->amount, 2) }}</div>@include('partials.status-badge', ['status' => $expense->status])</div>
    </div>
    <div class="detail-grid">
        <div class="detail-item"><div class="label">Expense Date</div><div class="value">{{ $expense->expense_date->format('d M Y') }}</div></div>
        <div class="detail-item"><div class="label">Month</div><div class="value">{{ $expense->month_date?->format('M Y') ?? '-' }}</div></div>
        <div class="detail-item wide"><div class="label">Description</div><div class="value font-weight-normal">{!! nl2br(e($expense->description ?: '-')) !!}</div></div>
    </div>
    <div class="form-actions mt-3">
        <a href="{{ route('employee.expenses.edit', $expense) }}" class="btn btn-info"><i class="fas fa-pen mr-1"></i> Edit</a>
        <form method="POST" action="{{ route('employee.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">@csrf @method('DELETE')<button class="btn btn-danger"><i class="fas fa-trash mr-1"></i> Delete</button></form>
    </div>
</div></div>
@endsection
