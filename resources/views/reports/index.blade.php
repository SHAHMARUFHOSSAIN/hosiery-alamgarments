@extends('layouts.admin')

@section('title', __('Reports'))

@section('header', __('Reports & Export'))

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
                <h6 class="text-muted mb-1">{{ __('Total Due') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($totalDues ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Due Collected') }}</h6>
                <h3 class="text-success mb-0">{{ format_currency($duePaymentCollection ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Prev. Due Pending') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($totalPrevDues ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Prev. Due Collected') }}</h6>
                <h3 class="text-success mb-0">{{ format_currency($totalPrevCollected ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Cash Received') }}</h6>
                <h3 class="text-success mb-0">{{ format_currency($paymentTotals->cash_total ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Cheque Reports') }}</h6>
                <h3 class="text-primary mb-0">{{ format_currency($paymentTotals->check_total ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('TT Reports') }}</h6>
                <h3 class="text-info mb-0">{{ format_currency($paymentTotals->tt_total ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Reference Card') }}</h6>
                <h3 class="text-secondary mb-0">{{ format_currency($paymentTotals->card_total ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Discount') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($totalDiscount ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Rep Discount') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($totalReportDiscount ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Due Discount') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($totalDueDiscount ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-dark">
            <div class="card-body text-center">
                <h6 class="text-white mb-1">{{ __('Total Discount') }}</h6>
                <h3 class="text-warning mb-0">{{ format_currency($totalDiscountAll ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary">
            <div class="card-body text-center">
                <h6 class="text-white mb-1">{{ __('Main Balance') }}</h6>
                <h3 class="text-white mb-0">{{ format_currency($mainBalance ?? 0) }}</h3>
            </div>
        </div>
    </div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-diagram-3 text-warning fs-2"></i>
            </div>
            <h5>{{ __('Resources') }}</h5>
            <p class="text-muted">{{ __('User-wise data with date filters — bills, payments, dues, collections') }}</p>
            <a href="{{ route('reports.resources') }}" class="btn btn-warning">{{ __('View Resources') }}</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-receipt text-primary fs-2"></i>
            </div>
            <h5>{{ __('Sales Report') }}</h5>
            <p class="text-muted">{{ __('View and export bill sales with date range and user filters') }}</p>
            <a href="{{ route('reports.sales') }}" class="btn btn-primary">{{ __('View Sales') }}</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-clock-history text-danger fs-2"></i>
            </div>
            <h5>{{ __('Total Dues') }}</h5>
            <p class="text-muted">{{ __('Combined due + cheque totals by bill date') }}</p>
            <a href="{{ route('reports.dues') }}" class="btn btn-danger">{{ __('View Total Dues') }}</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-dark bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-hourglass-split text-dark fs-2"></i>
            </div>
            <h5>{{ __('Previous Due Report') }}</h5>
            <p class="text-muted">{{ __('Track opening balance dues with payments and status') }}</p>
            <a href="{{ route('reports.previous-dues') }}" class="btn btn-dark">{{ __('View Prev. Dues') }}</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-graph-up text-success fs-2"></i>
            </div>
            <h5>{{ __('Daily Report') }}</h5>
            <p class="text-muted">{{ __('View dues due today with export options') }}</p>
            <a href="{{ route('dues.daily-report') }}" class="btn btn-success">{{ __('Daily Report') }}</a>
        </div>
    </div>
    @if(auth()->user()->isAdmin())
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-bar-chart-line text-info fs-2"></i>
            </div>
            <h5>{{ __('Data Analytics') }}</h5>
            <p class="text-muted">{{ __('Comprehensive business intelligence with charts and insights') }}</p>
            <a href="{{ route('reports.analytics') }}" class="btn btn-info text-white">{{ __('Analytics') }}</a>
        </div>
    </div>
    @endif
    @if(auth()->user()->isAdmin())
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-secondary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-person-x text-secondary fs-2"></i>
            </div>
            <h5>{{ __('Inactive Customers') }}</h5>
            <p class="text-muted">{{ __('Customers with no bill in last 30 days') }}</p>
            <a href="{{ route('reports.inactive-customers') }}" class="btn btn-secondary">{{ __('View Inactive') }}</a>
        </div>
    </div>
    @endif
</div>
@endsection
