@extends('layouts.admin')

@section('title', 'My Sales Report')

@section('header', 'My Sales Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user-reports.index') }}">My Reports</a></li>
        <li class="breadcrumb-item active">Sales</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Today Sales</h6>
                <h4 class="text-primary mb-0">৳{{ number_format($dailyAmount ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Today Discount</h6>
                <h4 class="text-danger mb-0">-৳{{ number_format($dailyDiscount ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Today Net</h6>
                <h4 class="text-success mb-0">৳{{ number_format($dailyAmount - $dailyDiscount, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Net (All)</h6>
                <h4 class="text-dark mb-0">৳{{ number_format($totalAmount - $totalDiscount, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('user-reports.sales') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bill No</th>
                    <th>Customer</th>
                    <th>Shop</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Net</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentDate = null;
                    $dailyTotal = 0;
                    $dailyDiscount = 0;
                @endphp
                @forelse($bills as $bill)
                    @if($currentDate !== $bill->created_at->format('Y-m-d') && $currentDate !== null)
                        <tr class="table-secondary">
                            <td colspan="3"><strong>Daily Total</strong></td>
                            <td class="text-end"><strong>৳{{ number_format($dailyTotal, 2) }}</strong></td>
                            <td class="text-end"><strong>-৳{{ number_format($dailyDiscount, 2) }}</strong></td>
                            <td class="text-end"><strong>৳{{ number_format($dailyTotal - $dailyDiscount, 2) }}</strong></td>
                            <td></td>
                        </tr>
                        @php $dailyTotal = 0; $dailyDiscount = 0; @endphp
                    @endif
                    @php $currentDate = $bill->created_at->format('Y-m-d'); @endphp
                    @php $dailyTotal += $bill->bill_amount; @endphp
                    @php $dailyDiscount += $bill->discount; @endphp
                    <tr>
                        <td><a href="{{ route('bills.show', $bill) }}" class="fw-semibold">{{ $bill->bill_no }}</a></td>
                        <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                        <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                        <td class="text-end">৳{{ number_format($bill->bill_amount, 2) }}</td>
                        <td class="text-end">৳{{ number_format($bill->discount, 2) }}</td>
                        <td class="text-end fw-bold">৳{{ number_format($bill->bill_amount - $bill->discount, 2) }}</td>
                        <td>{{ $bill->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3">No bills found</td></tr>
                @endforelse
                @if($currentDate !== null)
                    <tr class="table-secondary">
                        <td colspan="3"><strong>Daily Total</strong></td>
                        <td class="text-end"><strong>৳{{ number_format($dailyTotal, 2) }}</strong></td>
                        <td class="text-end"><strong>-৳{{ number_format($dailyDiscount, 2) }}</strong></td>
                        <td class="text-end"><strong>৳{{ number_format($dailyTotal - $dailyDiscount, 2) }}</strong></td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="table-primary">
                <tr>
                    <td colspan="3"><strong>Grand Total</strong></td>
                    <td class="text-end"><strong>৳{{ number_format($totalAmount, 2) }}</strong></td>
                    <td class="text-end"><strong>-৳{{ number_format($totalDiscount, 2) }}</strong></td>
                    <td class="text-end"><strong>৳{{ number_format($totalAmount - $totalDiscount, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @if($bills->hasPages())
    <div class="card-footer bg-white text-center">
        {!! str_replace('page-link', 'page-link btn btn-sm btn-outline-secondary', $bills->links()) !!}
    </div>
    @endif
</div>
@endsection