@extends('layouts.app')
@section('title', 'Edit Company Fund')
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.monthly-funds.update', $monthlyFund) }}">@method('PUT') @include('admin.monthly-funds._form')</form></div></div>@endsection
