@extends('layouts.admin')

@section('title', __('My Reports'))

@section('header', __('My Reports'))

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Sales') }}</h6>
                <h3 class="text-primary mb-0">{{ format_currency($totalSales ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Pending Dues') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($totalDue ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Collected Dues') }}</h6>
                <h3 class="text-success mb-0">{{ format_currency($paidDue ?? 0) }}</h3>
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
            <h5>{{ __('Sales Report') }}</h5>
            <p class="text-muted">{{ __('View your bill sales with date filters') }}</p>
            <a href="{{ route('user-reports.sales') }}" class="btn btn-primary">{{ __('View Sales') }}</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-clock-history text-danger fs-2"></i>
            </div>
            <h5>{{ __('Dues Report') }}</h5>
            <p class="text-muted">{{ __('Track your dues with status filters') }}</p>
            <a href="{{ route('user-reports.dues') }}" class="btn btn-danger">{{ __('View Dues') }}</a>
        </div>
    </div>
</div>
@endsection
