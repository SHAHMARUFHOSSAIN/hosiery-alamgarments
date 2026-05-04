@php
    $isAdmin = auth()->user()->isAdmin();
@endphp

@extends('layouts.admin')

@section('title', 'Admin Dashboard')

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
                <p class="text-muted mb-0">Total Customers</p>
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
                <p class="text-muted mb-0">Total Bills</p>
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
                <p class="text-muted mb-0">Pending Dues</p>
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
                <h5 class="mb-0">Recent Bills</h5>
                <a href="{{ route('bills.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bill No</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBills as $bill)
                        <tr>
                            <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                            <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                            <td>{{ number_format($bill->bill_amount, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ $bill->user->name ?? 'N/A' }}</span></td>
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
                <h5 class="mb-0">Pending Dues</h5>
                <a href="{{ route('dues.daily-report') }}" class="btn btn-sm btn-warning">Today</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Original</th>
                            <th>Remaining</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDues as $due)
                        <tr>
                            <td>{{ $due->customer->name ?? 'N/A' }}</td>
                            <td>{{ number_format($due->original_amount, 2) }}</td>
                            <td class="text-danger fw-bold">{{ number_format($due->remaining_amount, 2) }}</td>
                            <td><span class="badge bg-warning text-dark">{{ $due->due_date->format('M d') }}</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#dashPayModal{{ $due->id }}">
                                    <i class="bi bi-credit-card"></i> Pay
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3">No pending dues</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($recentDues as $due)
<div class="modal fade" id="dashPayModal{{ $due->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dues.add-payment') }}">
                @csrf
                <input type="hidden" name="due_id" value="{{ $due->id }}">
                <div class="modal-body">
                    <div class="mb-3 alert alert-warning">
                        <strong>Remaining:</strong> <span class="text-danger fw-bold">৳{{ number_format($due->remaining_amount, 2) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="payment_amount" class="form-control" 
                                   max="{{ $due->remaining_amount }}" value="{{ $due->remaining_amount }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Due Date <small class="text-muted">(if remaining balance)</small></label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection