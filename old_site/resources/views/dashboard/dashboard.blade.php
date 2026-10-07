@extends('Layouts.app')

@section('title', 'Dashboard')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Dashboard</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Dashboard v1</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        @foreach($userData as $user)
            <div class="col-md-4 col-sm-6 mb-4">
                <!-- User Card -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">{{ $user->name }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
                        <p class="card-text"><strong>Mobile:</strong> {{ $user->mobile }}</p>
                        <p class="card-text"><strong>Status:</strong> {{ $user->status }}</p>
                        <hr>
                        <h6 class="card-title">Total Expenses:</h6>
                        <p class="card-text">
                            <strong>₹{{ number_format($user->expenses_sum_amount, 2) }}</strong>
                        </p>
                    </div>
                    <div class="card-footer text-muted">
                        Created at: {{ \Carbon\Carbon::parse($user->created_at)->format('d M, Y') }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
