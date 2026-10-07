@extends('layouts.app')
@section('title', 'Edit Expense')
@section('content')<div class="card"><div class="card-body p-md-4"><form method="POST" action="{{ route('employee.expenses.update', $expense) }}">@method('PUT') @include('employee.expenses._form')</form></div></div>@endsection
