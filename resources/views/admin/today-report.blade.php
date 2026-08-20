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
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-receipt text-primary fs-2"></i>
                </div>
                <h3 class="mb-1">{{ format_number($totals['total_bills']) }}</h3>
                <p class="text-muted mb-0">{{ __('Total Bills') }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-currency-dollar text-success fs-2"></i>
                </div>
                <h3 class="mb-1">{{ format_currency($totals['gross_amount']) }}</h3>
                <p class="text-muted mb-0">{{ __('Gross Sales') }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-check-circle text-info fs-2"></i>
                </div>
                <h3 class="mb-1">{{ format_currency($totals['total_received']) }}</h3>
                <p class="text-muted mb-0">{{ __('Total Received') }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-clock-history text-danger fs-2"></i>
                </div>
                <h3 class="mb-1">{{ format_currency($totals['due_amount']) }}</h3>
                <p class="text-muted mb-0">{{ __('Pending Dues') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-calendar-event"></i> {{ __('Today Sales Summary') }} — {{ format_date($today) }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('User') }}</th>
                    <th class="text-end">{{ __('Bills') }}</th>
                    <th class="text-end">{{ __('Gross') }}</th>
                    <th class="text-end">{{ __('Discount') }}</th>
                    <th class="text-end">{{ __('Net') }}</th>
                    <th class="text-end">{{ __('Cash') }}</th>
                    <th class="text-end">{{ __('Cheque') }}</th>
                    <th class="text-end">{{ __('TT') }}</th>
                    <th class="text-end">{{ __('Card') }}</th>
                    <th class="text-end">{{ __('Received') }}</th>
                    <th class="text-end">{{ __('Due') }}</th>
                    <th class="text-center">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($userReports as $report)
                <tr>
                    <td>
                        <span class="fw-semibold">{{ $report['user']->name }}</span>
                    </td>
                    <td class="text-end">{{ format_number($report['total_bills']) }}</td>
                    <td class="text-end fw-bold">{{ format_currency($report['gross_amount']) }}</td>
                    <td class="text-end text-danger">{{ format_currency($report['bill_discount']) }}</td>
                    <td class="text-end fw-bold text-primary">{{ format_currency($report['net_amount']) }}</td>
                    <td class="text-end text-success">{{ format_currency($report['cash_amount']) }}</td>
                    <td class="text-end text-warning">{{ format_currency($report['cheque_amount']) }}</td>
                    <td class="text-end text-info">{{ format_currency($report['tt_amount']) }}</td>
                    <td class="text-end text-secondary">{{ format_currency($report['ref_card_amount']) }}</td>
                    <td class="text-end fw-bold text-success">{{ format_currency($report['total_received']) }}</td>
                    <td class="text-end fw-bold {{ $report['due_amount'] > 0 ? 'text-danger' : 'text-success' }}">{{ format_currency($report['due_amount']) }}</td>
                    <td class="text-center">
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
                    <td>{{ __('TOTAL') }}</td>
                    <td class="text-end">{{ format_number($totals['total_bills']) }}</td>
                    <td class="text-end">{{ format_currency($totals['gross_amount']) }}</td>
                    <td class="text-end text-danger">{{ format_currency($totals['bill_discount']) }}</td>
                    <td class="text-end text-primary">{{ format_currency($totals['net_amount']) }}</td>
                    <td class="text-end text-success">{{ format_currency($totals['cash_amount']) }}</td>
                    <td class="text-end text-warning">{{ format_currency($totals['cheque_amount']) }}</td>
                    <td class="text-end text-info">{{ format_currency($totals['tt_amount']) }}</td>
                    <td class="text-end text-secondary">{{ format_currency($totals['ref_card_amount']) }}</td>
                    <td class="text-end text-success">{{ format_currency($totals['total_received']) }}</td>
                    <td class="text-end text-danger">{{ format_currency($totals['due_amount']) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="row g-3">
    @forelse($userReports as $report)
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-person-circle"></i> {{ $report['user']->name }}
                </h6>
                @if($report['is_closed'])
                    <span class="badge bg-success">{{ __('Closed') }}</span>
                @elseif($report['total_bills'] > 0)
                    <span class="badge bg-warning text-dark">{{ __('Open') }}</span>
                @else
                    <span class="badge bg-secondary">{{ __('No Data') }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($report['total_bills'] > 0)
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold fs-5">{{ format_number($report['total_bills']) }}</div>
                            <small class="text-muted">{{ __('Bills') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold fs-5 text-primary">{{ format_currency($report['net_amount']) }}</div>
                            <small class="text-muted">{{ __('Net Sales') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold fs-5 {{ $report['due_amount'] > 0 ? 'text-danger' : 'text-success' }}">{{ format_currency($report['due_amount']) }}</div>
                            <small class="text-muted">{{ __('Due') }}</small>
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <div class="row g-2">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success bg-opacity-10 text-success p-2"><i class="bi bi-cash"></i></span>
                            <div>
                                <div class="fw-bold small">{{ format_currency($report['cash_amount']) }}</div>
                                <small class="text-muted">{{ __('Cash') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning bg-opacity-10 text-warning p-2"><i class="bi bi-bank"></i></span>
                            <div>
                                <div class="fw-bold small">{{ format_currency($report['cheque_amount']) }}</div>
                                <small class="text-muted">{{ __('Cheque') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info bg-opacity-10 text-info p-2"><i class="bi bi-bank"></i></span>
                            <div>
                                <div class="fw-bold small">{{ format_currency($report['tt_amount']) }}</div>
                                <small class="text-muted">{{ __('TT') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary p-2"><i class="bi bi-credit-card"></i></span>
                            <div>
                                <div class="fw-bold small">{{ format_currency($report['ref_card_amount']) }}</div>
                                <small class="text-muted">{{ __('Card') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if($report['bill_discount'] > 0)
                <div class="mt-3 p-2 bg-light rounded">
                    <small class="text-muted">{{ __('Discount') }}: <strong class="text-danger">{{ format_currency($report['bill_discount']) }}</strong></small>
                </div>
                @endif
                @else
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                    <small>{{ __('No bills today') }}</small>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2"></i>
                {{ __('No users found') }}
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
