@extends('layouts.admin')

@section('title', 'Previous Due #' . $previousDue->id)
@section('header', 'Previous Due #' . $previousDue->id)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('previous-dues.index') }}">Previous Dues</a></li>
        <li class="breadcrumb-item active">#{{ $previousDue->id }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted">Customer</th>
                        <td class="fw-semibold">{{ $previousDue->customer->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Mobile</th>
                        <td>{{ $previousDue->customer->mobile ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Original Amount</th>
                        <td class="fw-bold">৳{{ number_format($previousDue->original_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Total Paid</th>
                        <td class="fw-bold text-success">৳{{ number_format($previousDue->total_paid, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Remaining</th>
                        <td class="fw-bold text-danger fs-5">৳{{ number_format($previousDue->remaining_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status</th>
                        <td>
                            @if($previousDue->status == 'paid')
                            <span class="badge bg-success">Paid</span>
                            @else
                            <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Notes</th>
                        <td>{{ $previousDue->notes ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Created By</th>
                        <td>{{ $previousDue->creator->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Created At</th>
                        <td>{{ $previousDue->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Updated At</th>
                        <td>{{ $previousDue->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Customer Info</h5>
            </div>
            <div class="card-body">
                @if($previousDue->customer)
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted">Name</th>
                        <td>
                            <a href="{{ route('customers.show', $previousDue->customer) }}" class="fw-semibold">
                                {{ $previousDue->customer->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Mobile</th>
                        <td>{{ $previousDue->customer->mobile ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Opening Balance</th>
                        <td class="fw-bold {{ $previousDue->customer->opening_balance > 0 ? 'text-danger' : 'text-success' }}">
                            ৳{{ number_format($previousDue->customer->opening_balance, 2) }}
                        </td>
                    </tr>
                </table>
                @else
                <p class="text-muted mb-0">Customer not found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($previousDue->status == 'pending')
<div class="mb-4">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
        <i class="bi bi-credit-card"></i> Make Payment
    </button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Payment History</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Amount</th>
                    <th>Payment Type</th>
                    <th>Bank / Cheque</th>
                    <th>Remaining</th>
                    <th>Note</th>
                    <th>Date</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($previousDue->payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td class="fw-bold text-success">৳{{ number_format($payment->amount, 2) }}</td>
                    <td>
                        @php
                            $badge = match($payment->payment_type) {
                                'cash' => 'bg-success',
                                'check' => 'bg-warning text-dark',
                                'mobile_banking' => 'bg-info text-dark',
                                default => 'bg-secondary'
                            };
                            $label = match($payment->payment_type) {
                                'check' => 'CHEQUE',
                                'mobile_banking' => 'MOBILE BANKING',
                                default => strtoupper($payment->payment_type)
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td>
                        @if($payment->bank_name)
                            {{ $payment->bank_name }} @if($payment->check_no) ({{ $payment->check_no }}) @endif
                        @else
                            —
                        @endif
                    </td>
                    <td>৳{{ number_format($payment->remaining_amount, 2) }}</td>
                    <td>{{ $payment->note ?? '—' }}</td>
                    <td><small>{{ $payment->payment_date->format('M d, Y') }}</small></td>
                    <td><small>{{ $payment->user->name ?? 'N/A' }}</small></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4"><strong>No payments yet</strong></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <a href="{{ route('previous-dues.edit', $previousDue) }}" class="btn btn-primary">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <a href="{{ route('previous-dues.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

@if($previousDue->status == 'pending')
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('previous-dues.add-payment') }}">
                @csrf
                <input type="hidden" name="previous_due_id" value="{{ $previousDue->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Customer:</strong> {{ $previousDue->customer->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Original Amount:</strong> ৳{{ number_format($previousDue->original_amount, 2) }}
                    </div>
                    @if($previousDue->hasPartialPayments())
                    <div class="mb-3">
                        <strong>Total Paid:</strong> <span class="text-success">৳{{ number_format($previousDue->total_paid, 2) }}</span>
                    </div>
                    @endif
                    <div class="mb-3 alert alert-warning">
                        <strong>Remaining:</strong> <span class="text-danger fw-bold">৳{{ number_format($previousDue->remaining_amount, 2) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="payment_amount" class="form-control"
                                   max="{{ $previousDue->remaining_amount }}" value="{{ $previousDue->remaining_amount }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="check">Cheque</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" placeholder="Bank name (for cheque)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cheque No</label>
                        <input type="text" name="check_no" class="form-control" placeholder="Cheque number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction ID <small class="text-muted">(for reference)</small></label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN12345">
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
@endif
@endsection
