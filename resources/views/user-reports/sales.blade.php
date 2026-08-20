@extends('layouts.admin')

@section('title', __('My Sales Report'))

@section('header', __('My Sales Report'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user-reports.index') }}">{{ __('My Reports') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Sales') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Today Sales') }}</h6>
                <h4 class="text-primary mb-0">{{ format_currency($dailyAmount ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Today Discount') }}</h6>
                <h4 class="text-danger mb-0">-{{ format_currency($dailyDiscount ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Today Net') }}</h6>
                <h4 class="text-success mb-0">{{ format_currency($dailyAmount - $dailyDiscount) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Net (All)') }}</h6>
                <h4 class="text-dark mb-0">{{ format_currency($totalAmount - $totalDiscount) }}</h4>
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
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('user-reports.sales') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Shop') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Discount') }}</th>
                    <th class="text-end">{{ __('Net') }}</th>
                    <th>{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentDate = null;
                    $dailyTotal = 0;
                    $dailyDiscount = 0;
                @endphp
                @forelse($bills as $bill)
                    @php $billReportDate = $bill->report_date ?? $bill->created_at; @endphp
                    @if($currentDate !== $billReportDate->format('Y-m-d') && $currentDate !== null)
                        <tr class="table-secondary">
                            <td colspan="3"><strong>{{ __('Daily Total') }}</strong></td>
                            <td class="text-end"><strong>{{ format_currency($dailyTotal) }}</strong></td>
                            <td class="text-end"><strong>-{{ format_currency($dailyDiscount) }}</strong></td>
                            <td class="text-end"><strong>{{ format_currency($dailyTotal - $dailyDiscount) }}</strong></td>
                            <td></td>
                        </tr>
                        @php $dailyTotal = 0; $dailyDiscount = 0; @endphp
                    @endif
                    @php $currentDate = $billReportDate->format('Y-m-d'); @endphp
                    @php $dailyTotal += $bill->bill_amount; @endphp
                    @php $dailyDiscount += $bill->discount; @endphp
                    <tr>
                        <td><a href="{{ route('bills.show', $bill) }}" class="fw-semibold">{{ $bill->bill_no }}</a>
                            @if($bill->edited_at)
                                <span class="badge bg-warning text-dark ms-1" title="{{ __('Edited by') }} {{ $bill->editor?->name ?? __('Unknown') }}">{{ __('Edited') }}</span>
                            @endif
                        </td>
                        <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                        <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                        <td class="text-end">{{ format_currency($bill->bill_amount) }}</td>
                        <td class="text-end">{{ format_currency($bill->discount) }}</td>
                        <td class="text-end fw-bold">{{ format_currency($bill->bill_amount - $bill->discount) }}</td>
                        <td>{{ $billReportDate->format('M d, Y') }}</td>
                    </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3">{{ __('No bills found') }}</td></tr>
                @endforelse
                @if($currentDate !== null)
                    <tr class="table-secondary">
                        <td colspan="3"><strong>{{ __('Daily Total') }}</strong></td>
                        <td class="text-end"><strong>{{ format_currency($dailyTotal) }}</strong></td>
                        <td class="text-end"><strong>-{{ format_currency($dailyDiscount) }}</strong></td>
                        <td class="text-end"><strong>{{ format_currency($dailyTotal - $dailyDiscount) }}</strong></td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="table-primary">
                <tr>
                    <td colspan="3"><strong>{{ __('Grand Total') }}</strong></td>
                    <td class="text-end"><strong>{{ format_currency($totalAmount) }}</strong></td>
                    <td class="text-end"><strong>-{{ format_currency($totalDiscount) }}</strong></td>
                    <td class="text-end"><strong>{{ format_currency($totalAmount - $totalDiscount) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @if($bills->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $bills->links() !!}
    </div>
    @endif
</div>
@endsection
