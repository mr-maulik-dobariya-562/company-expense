@extends('layouts.app')
@section('title', 'Add Expense')
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('employee.expenses.store') }}">@include('employee.expenses._form')</form></div></div>@endsection
