@extends('layouts.admin')

@section('title', 'Reports')

@section('header', 'Reports & Export')

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
                <h6 class="text-muted mb-1">Total Due</h6>
                <h3 class="text-danger mb-0">৳{{ number_format($totalDues ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Due Collected</h6>
                <h3 class="text-success mb-0">৳{{ number_format($paidDues ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Prev. Due Pending</h6>
                <h3 class="text-danger mb-0">৳{{ number_format($totalPrevDues ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Prev. Due Collected</h6>
                <h3 class="text-success mb-0">৳{{ number_format($totalPrevCollected ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Main Balance</h6>
                <h3 class="text-{{ $mainBalance >= 0 ? 'success' : 'danger' }} mb-0">৳{{ number_format($mainBalance ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-receipt text-primary fs-2"></i>
            </div>
            <h5>Sales Report</h5>
            <p class="text-muted">View and export bill sales with date range and user filters</p>
            <a href="{{ route('reports.sales') }}" class="btn btn-primary">View Sales</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-clock-history text-danger fs-2"></i>
            </div>
            <h5>Dues Report</h5>
            <p class="text-muted">Track all dues with status and date filters</p>
            <a href="{{ route('reports.dues') }}" class="btn btn-danger">View Dues</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-dark bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-hourglass-split text-dark fs-2"></i>
            </div>
            <h5>Previous Due Report</h5>
            <p class="text-muted">Track opening balance dues with payments and status</p>
            <a href="{{ route('reports.previous-dues') }}" class="btn btn-dark">View Prev. Dues</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-graph-up text-success fs-2"></i>
            </div>
            <h5>Daily Report</h5>
            <p class="text-muted">View dues due today with export options</p>
            <a href="{{ route('dues.daily-report') }}" class="btn btn-success">Daily Report</a>
        </div>
    </div>
    @if(auth()->user()->isAdmin())
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-bar-chart-line text-info fs-2"></i>
            </div>
            <h5>Data Analytics</h5>
            <p class="text-muted">Comprehensive business intelligence with charts and insights</p>
            <a href="{{ route('reports.analytics') }}" class="btn btn-info text-white">Analytics</a>
        </div>
    </div>
    @endif
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-wallet2 text-warning fs-2"></i>
            </div>
            <h5>Main Balance</h5>
            <p class="text-muted">Manage main balance and transactions</p>
            <a href="{{ route('main-balance.index') }}" class="btn btn-warning">View Balance</a>
        </div>
    </div>
    @if(auth()->user()->isAdmin())
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-secondary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-person-x text-secondary fs-2"></i>
            </div>
            <h5>Inactive Customers</h5>
            <p class="text-muted">Customers with no bill in last 30 days</p>
            <a href="{{ route('reports.inactive-customers') }}" class="btn btn-secondary">View Inactive</a>
        </div>
    </div>
    @endif
</div>
@endsection