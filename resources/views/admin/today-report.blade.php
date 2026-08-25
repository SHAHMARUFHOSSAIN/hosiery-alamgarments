@extends('layouts.admin')

@section('title', __('Today Report'))

@section('header', __('Today Report'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Today Report') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row g-2 g-sm-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3 py-md-4 px-2 px-md-3">
                <div class="bg-primary bg-opacity-10 p-2 p-md-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-receipt text-primary fs-3 fs-md-2"></i>
                </div>
                <h3 class="mb-1 fs-4 fs-md-3">{{ format_number($totals['total_bills']) }}</h3>
                <p class="text-muted mb-0 small">{{ __('Total Bills') }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3 py-md-4 px-2 px-md-3">
                <div class="bg-success bg-opacity-10 p-2 p-md-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-currency-dollar text-success fs-3 fs-md-2"></i>
                </div>
                <h3 class="mb-1 fs-4 fs-md-3">{{ format_currency($totals['gross_amount']) }}</h3>
                <p class="text-muted mb-0 small">{{ __('Gross Sales') }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3 py-md-4 px-2 px-md-3">
                <div class="bg-info bg-opacity-10 p-2 p-md-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-check-circle text-info fs-3 fs-md-2"></i>
                </div>
                <h3 class="mb-1 fs-4 fs-md-3">{{ format_currency($totals['total_received']) }}</h3>
                <p class="text-muted mb-0 small">{{ __('Total Received') }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3 py-md-4 px-2 px-md-3">
                <div class="bg-danger bg-opacity-10 p-2 p-md-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-clock-history text-danger fs-3 fs-md-2"></i>
                </div>
                <h3 class="mb-1 fs-4 fs-md-3">{{ format_currency($totals['due_amount']) }}</h3>
                <p class="text-muted mb-0 small">{{ __('Pending Dues') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-2 py-md-3 px-3 px-md-4">
        <h5 class="mb-0 fs-6 fs-md-5"><i class="bi bi-calendar-event"></i> {{ __('Today Sales Summary') }} — {{ format_date($today) }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">{{ __('User') }}</th>
                    <th class="text-end">{{ __('Bills') }}</th>
                    <th class="text-end">{{ __('Gross') }}</th>
                    <th class="text-end">{{ __('Discount') }}</th>
                    <th class="text-end">{{ __('Net') }}</th>
                    <th class="text-end d-none d-sm-table-cell">{{ __('Cash') }}</th>
                    <th class="text-end d-none d-sm-table-cell">{{ __('Cheque') }}</th>
                    <th class="text-end d-none d-md-table-cell">{{ __('TT') }}</th>
                    <th class="text-end d-none d-md-table-cell">{{ __('Card') }}</th>
                    <th class="text-end">{{ __('Received') }}</th>
                    <th class="text-end">{{ __('Due') }}</th>
                    <th class="text-center pe-3">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($userReports as $report)
                <tr>
                    <td class="ps-3">
                        <span class="fw-semibold">{{ $report['user']->name }}</span>
                    </td>
                    <td class="text-end">{{ format_number($report['total_bills']) }}</td>
                    <td class="text-end fw-bold">{{ format_currency($report['gross_amount']) }}</td>
                    <td class="text-end text-danger">{{ format_currency($report['bill_discount']) }}</td>
                    <td class="text-end fw-bold text-primary">{{ format_currency($report['net_amount']) }}</td>
                    <td class="text-end text-success d-none d-sm-table-cell">{{ format_currency($report['cash_amount']) }}</td>
                    <td class="text-end text-warning d-none d-sm-table-cell">{{ format_currency($report['cheque_amount']) }}</td>
                    <td class="text-end text-info d-none d-md-table-cell">{{ format_currency($report['tt_amount']) }}</td>
                    <td class="text-end text-secondary d-none d-md-table-cell">{{ format_currency($report['ref_card_amount']) }}</td>
                    <td class="text-end fw-bold text-success">{{ format_currency($report['total_received']) }}</td>
                    <td class="text-end fw-bold {{ $report['due_amount'] > 0 ? 'text-danger' : 'text-success' }}">{{ format_currency($report['due_amount']) }}</td>
                    <td class="text-center pe-3">
                        @if($report['is_closed'])
                            <span class="badge bg-success">{{ __('Closed') }}</span>
                        @elseif($report['total_bills'] > 0)
                            <span class="badge bg-warning text-dark">{{ __('Open') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ __('No Data') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        {{ __('No users found') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($userReports->count() > 0)
            <tfoot class="table-light fw-bold">
                <tr>
                    <td class="ps-3">{{ __('TOTAL') }}</td>
                    <td class="text-end">{{ format_number($totals['total_bills']) }}</td>
                    <td class="text-end">{{ format_currency($totals['gross_amount']) }}</td>
                    <td class="text-end text-danger">{{ format_currency($totals['bill_discount']) }}</td>
                    <td class="text-end text-primary">{{ format_currency($totals['net_amount']) }}</td>
                    <td class="text-end text-success d-none d-sm-table-cell">{{ format_currency($totals['cash_amount']) }}</td>
                    <td class="text-end text-warning d-none d-sm-table-cell">{{ format_currency($totals['cheque_amount']) }}</td>
                    <td class="text-end text-info d-none d-md-table-cell">{{ format_currency($totals['tt_amount']) }}</td>
                    <td class="text-end text-secondary d-none d-md-table-cell">{{ format_currency($totals['ref_card_amount']) }}</td>
                    <td class="text-end text-success">{{ format_currency($totals['total_received']) }}</td>
                    <td class="text-end text-danger">{{ format_currency($totals['due_amount']) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
