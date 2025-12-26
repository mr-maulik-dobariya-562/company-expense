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
        @if (auth()->user()->hasPermissionTo("sale-order-box"))
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>Sale Order</h4>
                    <p style="padding: 0;margin: 0px;">Total : {{ $totalSaleOrder }}</p>
                    <!-- <p style="padding: 0;margin: 0px;">Completed : 0</p> -->
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <a href="{{ route('getSaleOrderData') }}" class="small-box-footer">Sale Order Info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif
        @if (auth()->user()->hasPermissionTo("sale-box"))
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>Sale Invoice</h4>
                    <p style="padding: 0;margin: 0px;">Total : {{ $totalSale }}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="{{ route('getSaleData') }}" class="small-box-footer">Sale Info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif
        @if (auth()->user()->hasPermissionTo("purchase-box"))
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>Purchase Invoice</h4>
                    <p style="padding: 0;margin: 0px;">Total : {{ $totalPurchase }}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="{{ route('getPurchaseData') }}" class="small-box-footer">Purchase Info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif
    </div>
@endsection
