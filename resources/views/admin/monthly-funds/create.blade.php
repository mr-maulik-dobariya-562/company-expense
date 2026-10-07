@extends('layouts.app')
@section('title', 'Add Company Fund')
@section('content')<div class="card"><div class="card-body p-md-4"><form method="POST" action="{{ route('admin.monthly-funds.store') }}">@include('admin.monthly-funds._form')</form></div></div>@endsection
