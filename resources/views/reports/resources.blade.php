@extends('layouts.admin')

@section('title', __('Resources Report'))

@section('header', __('Resources Report'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('Reports') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Resources') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">{{ __('User') }}</label>
                <select name="user_id" class="form-select">
                    <option value="">{{ __('All Users') }}</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('Date From') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('Date To') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('reports.resources') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Bills') }}</h6>
                <h3 class="text-primary mb-0">{{ $totalBills }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Gross Amount') }}</h6>
                <h3 class="text-primary mb-0">{{ format_currency($grossAmount) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Discount') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($totalDiscount) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Rep Discount') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($repDiscount ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Due Discount') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency(($duePayDiscount ?? 0) + ($prevDuePayDiscount ?? 0) + ($chequeDiscount ?? 0)) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Net Amount') }}</h6>
                <h3 class="text-success mb-0">{{ format_currency($grossAmount - $totalDiscount) }}</h3>
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
                <h6 class="text-muted mb-1">{{ __('Cheque (Pending)') }}</h6>
                <h3 class="text-warning mb-0">{{ format_currency(($paymentTotals->check_total ?? 0) - ($chequeEncashed ?? 0) - ($chequeDiscount ?? 0)) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Cheque (Encashed)') }}</h6>
                <h3 class="text-primary mb-0">{{ format_currency($chequeEncashed ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('TT') }}</h6>
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
                <h6 class="text-muted mb-1">{{ __('Dues Pending') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($duePendingAmount ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Due Collected') }}</h6>
                <h3 class="text-success mb-0">{{ format_currency($dueCollection ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary">
            <div class="card-body text-center">
                <h6 class="text-white mb-1">{{ __('Main Balance') }}</h6>
                @php
                    $mainBalance = ($paymentTotals->cash_total ?? 0)
                        + ($paymentTotals->tt_total ?? 0)
                        + ($chequeEncashed ?? 0)
                        + (($paymentTotals->card_total ?? 0))
                        + ($dueCollection ?? 0);
                @endphp
                <h3 class="text-white mb-0">{{ format_currency($mainBalance) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Bill Details') }} ({{ $bills->total() }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Bill Date') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Shop') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Discount') }}</th>
                    <th>{{ __('Net') }}</th>
                    <th>{{ __('Payment') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Entry Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                <tr>
                    <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                    <td>{{ $bill->report_date?->format('d/m/Y') ?? 'N/A' }}</td>
                    <td>{{ $bill->customer?->name ?? 'N/A' }}</td>
                    <td>{{ $bill->customer?->location ?? 'N/A' }}</td>
                    <td>{{ $bill->shop_name ?? '-' }}</td>
                    <td>{{ format_currency($bill->bill_amount) }}</td>
                    <td>{{ format_currency($bill->discount) }}</td>
                    <td>{{ format_currency($bill->bill_amount - $bill->discount) }}</td>
                    <td>
                        @php
                            $types = $bill->payments->pluck('payment_type')->unique();
                        @endphp
                        @foreach($types as $t)
                            <span class="badge bg-{{ $t == 'cash' ? 'success' : ($t == 'check' ? 'warning text-dark' : ($t == 'tt' ? 'info' : ($t == 'card' ? 'secondary' : 'danger'))) }} me-1">
                                {{ ucfirst($t) }}
                            </span>
                        @endforeach
                    </td>
                    <td>{{ $bill->user?->name ?? 'N/A' }}</td>
                    <td>{{ $bill->created_at?->format('d/m/Y') ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">{{ __('No bills found for the selected filters.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bills->hasPages())
    <div class="card-footer bg-white">
        {{ $bills->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
