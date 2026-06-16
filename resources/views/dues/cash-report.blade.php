@extends('layouts.admin')

@section('title', 'Cash Received Reports')

@section('header', 'Cash Received Reports')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Cash Received Reports</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Bill no/customer..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="encashed" {{ request('status') == 'encashed' ? 'selected' : '' }}>Received</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('dues.cash-report') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Cash Received</h6>
                <h3 class="text-primary mb-0">৳{{ number_format($totalCashAmount, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Payments</h6>
                <h3 class="text-success mb-0">{{ $totalPayments }}</h3>
            </div>
        </div>
    </div>
</div>

<h5 class="mb-3">Cash Receipts ({{ $cashPayments->total() }})</h5>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bill No</th>
                    <th>Customer</th>
                    <th>Details</th>
                    <th>
                        <a href="{{ route('dues.cash-report', ['sort' => 'amount', 'direction' => request('sort') == 'amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Amount @if(request('sort') == 'amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('dues.cash-report', ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Date @if(request('sort') == 'created_at'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('dues.cash-report', ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Status @if(request('sort') == 'status'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashPayments as $payment)
                <tr>
                    <td><a href="{{ route('bills.show', $payment->bill) }}">{{ $payment->bill->bill_no ?? 'N/A' }}</a></td>
                    <td>{{ $payment->bill->customer->name ?? 'N/A' }}</td>
                    <td>{{ $payment->details ?? 'N/A' }}</td>
                    <td class="fw-bold">৳{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                    <td>
                        @if($payment->status === 'encashed')
                        <span class="badge bg-success">Received</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('bills.show', $payment->bill) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3">No cash payments found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cashPayments->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $cashPayments->appends(request()->only('status', 'date_from', 'date_to', 'search', 'sort', 'direction'))->links() !!}
    </div>
    @endif
</div>
@endsection
