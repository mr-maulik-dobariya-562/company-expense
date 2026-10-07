@extends('layouts.app')
@section('title', 'Add Employee')
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.employees.store') }}">@include('admin.employees._form')</form></div></div>@endsection
