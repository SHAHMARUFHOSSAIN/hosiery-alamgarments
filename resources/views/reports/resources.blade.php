@extends('layouts.admin')

@section('title', 'Resources Report')

@section('header', 'Resources Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active">Resources</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('reports.resources') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Bills</h6>
                <h3 class="text-primary mb-0">{{ $totalBills }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Gross Amount</h6>
                <h3 class="text-primary mb-0">৳{{ number_format($grossAmount, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Discount</h6>
                <h3 class="text-danger mb-0">৳{{ number_format($totalDiscount, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Net Amount</h6>
                <h3 class="text-success mb-0">৳{{ number_format($grossAmount - $totalDiscount, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Cash Received</h6>
                <h3 class="text-success mb-0">৳{{ number_format($paymentTotals->cash_total ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Cheque (Pending)</h6>
                <h3 class="text-warning mb-0">৳{{ number_format(($paymentTotals->check_total ?? 0) - ($chequeEncashed ?? 0), 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Cheque (Encashed)</h6>
                <h3 class="text-primary mb-0">৳{{ number_format($chequeEncashed ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">TT</h6>
                <h3 class="text-info mb-0">৳{{ number_format($paymentTotals->tt_total ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Reference Card</h6>
                <h3 class="text-secondary mb-0">৳{{ number_format($paymentTotals->card_total ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Due (Pending)</h6>
                <h3 class="text-danger mb-0">৳{{ number_format($paymentTotals->due_total ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Due Collected</h6>
                <h3 class="text-success mb-0">৳{{ number_format($dueCollection ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary">
            <div class="card-body text-center">
                <h6 class="text-white mb-1">Main Balance</h6>
                @php
                    $mainBalance = ($paymentTotals->cash_total ?? 0)
                        + ($paymentTotals->tt_total ?? 0)
                        + ($chequeEncashed ?? 0)
                        + (($paymentTotals->card_total ?? 0))
                        + ($dueCollection ?? 0);
                @endphp
                <h3 class="text-white mb-0">৳{{ number_format($mainBalance, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Bill Details ({{ $bills->total() }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bill No</th>
                    <th>Customer</th>
                    <th>Shop</th>
                    <th>Amount</th>
                    <th>Discount</th>
                    <th>Net</th>
                    <th>Payment</th>
                    <th>User</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                <tr>
                    <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                    <td>{{ $bill->customer?->name ?? 'N/A' }}</td>
                    <td>{{ $bill->shop_name ?? '-' }}</td>
                    <td>৳{{ number_format($bill->bill_amount, 2) }}</td>
                    <td>৳{{ number_format($bill->discount, 2) }}</td>
                    <td>৳{{ number_format($bill->bill_amount - $bill->discount, 2) }}</td>
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
                    <td>{{ $bill->report_date?->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No bills found for the selected filters.</td>
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