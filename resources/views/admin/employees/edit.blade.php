@extends('layouts.app')
@section('title', 'Edit Employee')
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.employees.update', $employee) }}">@method('PUT') @include('admin.employees._form')</form></div></div>@endsection
