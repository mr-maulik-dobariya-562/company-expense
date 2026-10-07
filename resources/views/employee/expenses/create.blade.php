@extends('layouts.app')
@section('title', 'Add Expense')
@section('content')<div class="card"><div class="card-body p-md-4"><form method="POST" action="{{ route('employee.expenses.store') }}" data-ajax-expense novalidate>@include('employee.expenses._form')</form></div></div>@endsection

@push('scripts')
<script src="{{ asset('js/expense-form.js') }}"></script>
@endpush
