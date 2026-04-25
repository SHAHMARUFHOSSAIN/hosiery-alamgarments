@extends('layouts.admin')

@section('title', 'Dashboard')

@section('header', 'Dashboard')

@section('content')
@if($stats['todayDues'] > 0)
<div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle fs-4"></i>
    <div>
        <strong>{{ $stats['todayDues'] }} dues</strong> due today worth {{ number_format($stats['totalDues'], 2) }}
        <a href="{{ route('dues.daily-report') }}" class="alert-link">View Report</a>
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-people text-primary fs-2"></i>
                </div>
                <h3 class="mb-1">{{ number_format($stats['totalCustomers']) }}</h3>
                <p class="text-muted mb-0">My Customers</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-receipt text-success fs-2"></i>
                </div>
                <h3 class="mb-1">{{ number_format($stats['totalBills']) }}</h3>
                <p class="text-muted mb-0">My Bills</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-currency-dollar text-danger fs-2"></i>
                </div>
                <h3 class="mb-1 text-danger">{{ number_format($stats['totalDues'], 2) }}</h3>
                <p class="text-muted mb-0">My Pending Dues</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-calendar-event text-warning fs-2"></i>
                </div>
                <h3 class="mb-1">{{ number_format($stats['todayDues']) }}</h3>
                <p class="text-muted mb-0">Due Today</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Recent Bills</h5>
                <a href="{{ route('bills.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bill No</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBills as $bill)
                        <tr>
                            <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                            <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                            <td>{{ number_format($bill->bill_amount, 2) }}</td>
                            <td>{{ $bill->created_at->format('M d') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3">No bills found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Pending Dues</h5>
                <a href="{{ route('dues.daily-report') }}" class="btn btn-sm btn-warning">Today</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDues as $due)
                        <tr>
                            <td>{{ $due->customer->name ?? 'N/A' }}</td>
                            <td class="text-danger fw-bold">{{ number_format($due->amount, 2) }}</td>
                            <td><span class="badge bg-warning text-dark">{{ $due->due_date->format('M d') }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('dues.mark-paid', $due) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success py-0 px-2">Paid</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3">No pending dues</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection