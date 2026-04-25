@extends('layouts.admin')

@section('title', 'Bill: ' . $bill->bill_no)

@section('header', 'Bill Details')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('bills.index') }}">Bills</a></li>
        <li class="breadcrumb-item active">{{ $bill->bill_no }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Bill: {{ $bill->bill_no }}</h2>
    <div>
        <a href="{{ route('bills.edit', $bill) }}" class="btn btn-secondary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Bill Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Bill No:</th><td>{{ $bill->bill_no }}</td></tr>
                    <tr><th>Customer:</th><td><a href="{{ route('customers.show', $bill->customer) }}">{{ $bill->customer->name ?? 'N/A' }}</a></td></tr>
                    <tr><th>Shop:</th><td>{{ $bill->shop_name ?? 'N/A' }}</td></tr>
                    <tr><th>Amount:</th><td class="fw-bold">{{ number_format($bill->bill_amount, 2) }}</td></tr>
                    <tr><th>Discount:</th><td>{{ number_format($bill->discount, 2) }}</td></tr>
                    <tr><th>Net:</th><td class="fw-bold text-success">{{ number_format($bill->bill_amount - $bill->discount, 2) }}</td></tr>
                    <tr><th>User:</th><td><span class="badge bg-secondary">{{ $bill->user->name ?? 'N/A' }}</span></td></tr>
                    <tr><th>Date:</th><td>{{ $bill->created_at->format('M d, Y h:i A') }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Payments</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Type</th><th>Amount</th><th>Details</th><th>Due Date</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @forelse($bill->payments as $payment)
                        <tr>
                            <td><span class="badge bg-{{ $payment->payment_type === 'due' ? 'danger' : 'primary' }}">
                                {{ strtoupper($payment->payment_type) }}
                            </span></td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->details ?? 'N/A' }}</td>
                            <td>{{ $payment->due_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3">No payments found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Dues</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Amount</th><th>Due Date</th><th>Status</th><th>User</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($bill->dues as $due)
                        <tr>
                            <td class="text-danger fw-bold">{{ number_format($due->amount, 2) }}</td>
                            <td><span class="badge bg-{{ $due->due_date->isPast() ? 'danger' : 'warning' }} text-dark">{{ $due->due_date->format('M d, Y') }}</span></td>
                            <td><span class="badge bg-{{ $due->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($due->status) }}</span></td>
                            <td><span class="badge bg-secondary">{{ $due->creator->name ?? 'N/A' }}</span></td>
                            <td>
                                @if($due->status === 'pending')
                                <form method="POST" action="{{ route('dues.mark-paid', $due) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success py-0 px-2">Mark Paid</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3">No dues found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection