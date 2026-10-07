@extends('layouts.app')
@section('title', 'Expense Detail')
@section('content')
<div class="card"><div class="card-body">
<dl class="row"><dt class="col-sm-3">Employee</dt><dd class="col-sm-9">{{ $expense->user->name }}</dd><dt class="col-sm-3">Title</dt><dd class="col-sm-9">{{ $expense->title }}</dd><dt class="col-sm-3">Description</dt><dd class="col-sm-9">{{ $expense->description ?: '-' }}</dd><dt class="col-sm-3">Date</dt><dd class="col-sm-9">{{ $expense->expense_date->format('d M Y') }}</dd><dt class="col-sm-3">Amount</dt><dd class="col-sm-9">₹{{ number_format($expense->amount, 2) }}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ ucfirst($expense->status) }}</dd></dl>
<a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">Back</a>
</div></div>
@endsection
