@extends('layouts.admin')

@section('title', 'My Reports')

@section('header', 'My Reports')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Sales</h6>
                <h3 class="text-primary mb-0">৳{{ number_format($totalSales ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Pending Dues</h6>
                <h3 class="text-danger mb-0">৳{{ number_format($totalDue ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Collected Dues</h6>
                <h3 class="text-success mb-0">৳{{ number_format($paidDue ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-receipt text-primary fs-2"></i>
            </div>
            <h5>Sales Report</h5>
            <p class="text-muted">View your bill sales with date filters</p>
            <a href="{{ route('user-reports.sales') }}" class="btn btn-primary">View Sales</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-clock-history text-danger fs-2"></i>
            </div>
            <h5>Dues Report</h5>
            <p class="text-muted">Track your dues with status filters</p>
            <a href="{{ route('user-reports.dues') }}" class="btn btn-danger">View Dues</a>
        </div>
    </div>
</div>
@endsection