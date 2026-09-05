@extends('layouts.admin')

@section('title', __('Sales Details') . ' - ' . $user->name)
@section('header', $user->name)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.today-report', ['date' => $date]) }}">{{ __('Today Report') }}</a></li>
        <li class="breadcrumb-item active">{{ $user->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
@php
    $isClosed = $closedReport && $closedReport->status === 'closed';
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex align-items-center justify-content-center">
                    <i class="bi bi-person-badge text-primary fs-4"></i>
                </div>
                <div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <div class="text-muted small">
                        <i class="bi bi-calendar-event"></i> {{ __('Sales of') }} {{ format_date($date, 'M d, Y') }}
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $isClosed ? 'bg-success' : ($totalBills > 0 ? 'bg-warning text-dark' : 'bg-secondary') }} fs-6">
                    {{ $isClosed ? __('Closed') : ($totalBills > 0 ? __('Open') : __('No Data')) }}
                </span>
                <a href="{{ route('admin.today-report', ['date' => $date]) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> {{ __('Back to Summary') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 g-sm-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3 py-md-4 px-2 px-md-3">
                <div class="bg-primary bg-opacity-10 p-2 p-md-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-receipt text-primary fs-3 fs-md-2"></i>
                </div>
                <h3 class="mb-1 fs-4 fs-md-3">{{ format_number($totalBills) }}</h3>
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
                <h3 class="mb-1 fs-4 fs-md-3">{{ format_currency($grossAmount) }}</h3>
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
                <h3 class="mb-1 fs-4 fs-md-3">{{ format_currency($totalReceived) }}</h3>
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
                <h3 class="mb-1 fs-4 fs-md-3">{{ format_currency($dueAmt) }}</h3>
                <p class="text-muted mb-0 small">{{ __('Pending Dues') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 g-sm-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-1 small">{{ __('Cash') }}</h6>
                <h5 class="mb-0 text-success">{{ format_currency($cashAmt) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-1 small">{{ __('Cheque') }}</h6>
                <h5 class="mb-0 text-warning">{{ format_currency($chequeAmt) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-1 small">{{ __('TT') }}</h6>
                <h5 class="mb-0 text-info">{{ format_currency($ttAmt) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-1 small">{{ __('Card') }}</h6>
                <h5 class="mb-0 text-secondary">{{ format_currency($refCardAmt) }}</h5>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-2 py-md-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fs-6 fs-md-5"><i class="bi bi-receipt-cutoff"></i> {{ __('All Bills') }} — {{ $user->name }}</h5>
        <span class="badge bg-primary">{{ format_number($totalBills) }} {{ __('bills') }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">{{ __('Bill No') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Shop') }}</th>
                    <th class="text-end">{{ __('Bill Amount') }}</th>
                    <th class="text-end">{{ __('Discount') }}</th>
                    <th class="text-end">{{ __('Net') }}</th>
                    <th class="text-end">{{ __('Cash') }}</th>
                    <th class="text-end">{{ __('Cheque') }}</th>
                    <th class="text-end">{{ __('TT') }}</th>
                    <th class="text-end">{{ __('Card') }}</th>
                    <th class="text-end">{{ __('Received') }}</th>
                    <th class="text-end">{{ __('Due') }}</th>
                    <th class="text-center pe-3">{{ __('Payment') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                @php
                    $payments = $bill->payments;
                    $cashAmts = $payments->where('payment_type', 'cash')->sum('amount');
                    $checkAmts = $payments->where('payment_type', 'check')->sum('amount');
                    $cardAmts = $payments->where('payment_type', 'card')->sum('amount');
                    $ttAmts = $payments->where('payment_type', 'tt')->sum('amount');
                    $received = $payments->sum('amount');
                    $firstPayment = $payments->first();
                    $paymentType = $firstPayment?->payment_type;
                    $billNet = $bill->bill_amount - $bill->discount;
                    $billDue = $bill->dues->where('status', 'pending')->sum('original_amount');
                @endphp
                <tr>
                    <td class="ps-3"><a href="{{ route('bills.show', $bill) }}" class="fw-semibold text-decoration-none">{{ $bill->bill_no }}</a></td>
                    <td>{{ $bill->customer?->name ?? 'N/A' }}</td>
                    <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                    <td class="text-end">{{ format_currency($bill->bill_amount) }}</td>
                    <td class="text-end text-danger">{{ format_currency($bill->discount) }}</td>
                    <td class="text-end fw-bold text-primary">{{ format_currency($billNet) }}</td>
                    <td class="text-end text-success">{{ $cashAmts > 0 ? format_currency($cashAmts) : '-' }}</td>
                    <td class="text-end text-warning">{{ $checkAmts > 0 ? format_currency($checkAmts) : '-' }}</td>
                    <td class="text-end text-info">{{ $ttAmts > 0 ? format_currency($ttAmts) : '-' }}</td>
                    <td class="text-end text-secondary">{{ $cardAmts > 0 ? format_currency($cardAmts) : '-' }}</td>
                    <td class="text-end fw-bold text-success">{{ format_currency($received) }}</td>
                    <td class="text-end fw-bold {{ $billDue > 0 ? 'text-danger' : 'text-success' }}">{{ format_currency($billDue) }}</td>
                    <td class="text-center pe-3">
                        @if($paymentType === 'check')
                            <span class="badge bg-warning text-dark">{{ __('CHEQUE') }}</span>
                        @elseif($paymentType === 'due')
                            <span class="badge bg-danger">{{ __('DUE') }}</span>
                        @elseif($paymentType === 'tt')
                            <span class="badge bg-info text-dark">{{ __('TT') }}</span>
                        @elseif($paymentType === 'cash')
                            <span class="badge bg-success">{{ __('CASH') }}</span>
                        @elseif($paymentType === 'card')
                            <span class="badge bg-secondary">{{ __('CARD') }}</span>
                        @else
                            <span class="badge bg-secondary">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        {{ __('No bills found for') }} {{ format_date($date, 'M d, Y') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($bills->count() > 0)
            <tfoot class="table-light fw-bold">
                <tr>
                    <td class="ps-3" colspan="3">{{ __('Total') }} ({{ format_number($totalBills) }})</td>
                    <td class="text-end">{{ format_currency($grossAmount) }}</td>
                    <td class="text-end text-danger">{{ format_currency($billDiscount) }}</td>
                    <td class="text-end text-primary">{{ format_currency($netAmount) }}</td>
                    <td class="text-end text-success">{{ format_currency($cashAmt) }}</td>
                    <td class="text-end text-warning">{{ format_currency($chequeAmt) }}</td>
                    <td class="text-end text-info">{{ format_currency($ttAmt) }}</td>
                    <td class="text-end text-secondary">{{ format_currency($refCardAmt) }}</td>
                    <td class="text-end text-success">{{ format_currency($totalReceived) }}</td>
                    <td class="text-end text-danger">{{ format_currency($dueAmt) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($bills->count() > 0)
    <div class="card-footer bg-white text-center small text-muted">
        <i class="bi bi-info-circle"></i> {{ __('Showing all') }} {{ format_number($totalBills) }} {{ __('bills for this report — no pagination.') }}
    </div>
    @endif
</div>
@endsection