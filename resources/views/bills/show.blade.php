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
                    <tr><th>Bill Man:</th><td>{{ $bill->bill_man ?? 'N/A' }}</td></tr>
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
                            <td><span class="badge bg-{{ $payment->payment_type === 'due' ? 'danger' : ($payment->payment_type === 'check' ? 'warning text-dark' : ($payment->payment_type === 'tt' ? 'info text-dark' : ($payment->payment_type === 'card' ? 'secondary text-white' : 'primary'))) }}">
                                {{ strtoupper($payment->payment_type) }}
                            </span></td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->details ?? 'N/A' }}</td>
                            <td>{{ $payment->due_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                        </tr>
                        @if($payment->payment_type === 'check')
                        <tr class="table-warning">
                            <td colspan="5">
                                <div class="row g-2">
                                    <div class="col-md-2"><strong>Bank:</strong> {{ $payment->bank_name ?? 'N/A' }}</div>
                                    <div class="col-md-2"><strong>Check No:</strong> {{ $payment->check_no ?? 'N/A' }}</div>
                                    <div class="col-md-2"><strong>Check Amt:</strong> {{ number_format($payment->check_amount, 2) }}</div>
                                    <div class="col-md-2"><strong>Check Date:</strong> {{ $payment->check_date?->format('M d, Y') ?? 'N/A' }}</div>
                                    <div class="col-md-2"><strong>Reminder:</strong> {{ $payment->check_reminder_date?->format('M d, Y') ?? 'N/A' }}</div>
                                    <div class="col-md-2"><strong>Status:</strong> 
                                        @if($payment->status === 'encashed')
                                        <span class="badge bg-success">Encashed</span>
                                        @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </div>
                                    @if($payment->check_photo)
                                    <div class="col-12"><a href="{{ asset('storage/' . $payment->check_photo) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-image"></i> View Check Photo</a></div>
                                    @endif
                                    @if($payment->status === 'pending' && $payment->check_amount > 0)
                                    <div class="col-12">
                                        <form method="POST" action="{{ route('dues.encash', $payment) }}" class="d-inline" 
                                              onsubmit="return confirm('Mark this check as encashed?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i> Encash Check
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                        @if($payment->payment_type === 'tt')
                        <tr class="table-info">
                            <td colspan="5">
                                <div class="row g-2">
                                    <div class="col-md-3"><strong>Bank:</strong> {{ $payment->tt_bank_name ?? 'N/A' }}</div>
                                    <div class="col-md-3"><strong>Account No:</strong> {{ $payment->tt_account_no ?? 'N/A' }}</div>
                                    <div class="col-md-3"><strong>TT Amt:</strong> {{ number_format($payment->tt_amount, 2) }}</div>
                                    <div class="col-md-3"><strong>TT Date:</strong> {{ $payment->tt_date?->format('M d, Y') ?? 'N/A' }}</div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @if($payment->payment_type === 'card')
                        <tr class="table-secondary">
                            <td colspan="5">
                                <div class="row g-2">
                                    <div class="col-md-3"><strong>Name:</strong> {{ $payment->card_name ?? 'N/A' }}</div>
                                    <div class="col-md-3"><strong>Location:</strong> {{ $payment->card_location ?? 'N/A' }}</div>
                                    <div class="col-md-3"><strong>Card Amt:</strong> {{ number_format($payment->card_amount, 2) }}</div>
                                    <div class="col-md-3"><strong>Card Date:</strong> {{ $payment->card_date?->format('M d, Y') ?? 'N/A' }}</div>
                                </div>
                            </td>
                        </tr>
                        @endif
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
                        <tr>
                            <th>Original</th>
                            <th>Paid</th>
                            <th>Remaining</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bill->dues as $due)
                        <tr>
                            <td>৳{{ number_format($due->original_amount, 2) }}</td>
                            <td class="text-success fw-bold">৳{{ number_format($due->total_paid, 2) }}</td>
                            <td class="text-danger fw-bold">৳{{ number_format($due->remaining_amount, 2) }}</td>
                            <td><span class="badge bg-{{ $due->due_date->isPast() ? 'danger' : 'warning' }} text-dark">{{ $due->due_date->format('M d, Y') }}</span></td>
                            <td>
                                @if($due->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                                @elseif($due->hasPartialPayments())
                                <span class="badge bg-info text-dark">Partial</span>
                                @else
                                <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($due->status == 'pending')
                                <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#billDuePayModal{{ $due->id }}">
                                    <i class="bi bi-credit-card"></i> Pay
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-3">No dues found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($bill->dues as $due)
@if($due->status === 'pending')
<div class="modal fade" id="billDuePayModal{{ $due->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dues.add-payment') }}">
                @csrf
                <input type="hidden" name="due_id" value="{{ $due->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Original Amount:</strong> ৳{{ number_format($due->original_amount, 2) }}
                    </div>
                    @if($due->hasPartialPayments())
                    <div class="mb-3">
                        <strong>Total Paid:</strong> <span class="text-success">৳{{ number_format($due->total_paid, 2) }}</span>
                    </div>
                    @endif
                    <div class="mb-3 alert alert-warning">
                        <strong>Remaining:</strong> <span class="text-danger fw-bold">৳{{ number_format($due->remaining_amount, 2) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="payment_amount" class="form-control" 
                                   max="{{ $due->remaining_amount }}" value="{{ $due->remaining_amount }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Due Date <small class="text-muted">(if remaining balance)</small></label>
                        <input type="date" name="next_due_date" class="form-control">
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
@endforeach
@endsection