@extends('layouts.admin')

@section('title', 'Daily Due Report')

@section('header', 'Daily Due Report - ' . now()->format('M d, Y'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('dues.index') }}">Dues</a></li>
        <li class="breadcrumb-item active">Daily Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">Due Today: {{ $todayDues->total() }}</h2>
    <div>
        <a href="{{ route('dues.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-warning text-dark py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-event"></i> 
                Total: {{ number_format($totalAmount, 2) }}
            </h5>
            <a href="{{ route('export.dues', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-sm btn-success">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Original</th>
                    <th>Paid</th>
                    <th>Remaining</th>
                    <th>Due Date</th>
                    <th>User</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayDues as $due)
                <tr>
                    <td>{{ $due->id }}</td>
                    <td><a href="{{ route('customers.show', $due->customer) }}" class="fw-semibold">{{ $due->customer->name ?? 'N/A' }}</a></td>
                    <td>{{ $due->customer->mobile ?? 'N/A' }}</td>
                    <td>{{ number_format($due->original_amount, 2) }}</td>
                    <td class="text-success fw-bold">{{ number_format($due->total_paid, 2) }}</td>
                    <td class="text-danger fw-bold fs-5">{{ number_format($due->remaining_amount, 2) }}</td>
                    <td><span class="badge bg-danger text-white">{{ $due->due_date->format('M d, Y') }}</span></td>
                    <td><span class="badge bg-secondary">{{ $due->creator->name ?? 'N/A' }}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#dailyPayModal{{ $due->id }}">
                            <i class="bi bi-credit-card"></i> Pay
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-success">
                        <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                        <strong>No pending dues for today!</strong>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($todayDues->total() > 0)
            <tfoot class="table-dark">
                <tr>
                    <td colspan="5" class="text-end"><strong>Total Remaining:</strong></td>
                    <td class="text-danger fw-bold fs-5">{{ number_format($totalAmount, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($todayDues->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $todayDues->links() !!}
    </div>
    @endif
</div>

@foreach($todayDues as $due)
<div class="modal fade" id="dailyPayModal{{ $due->id }}" tabindex="-1">
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
                        <strong>Customer:</strong> {{ $due->customer->name ?? 'N/A' }}
                    </div>
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
                            <option value="check">Cheque</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Due Date <small class="text-muted">(if remaining balance)</small></label>
                        <input type="date" name="next_due_date" class="form-control">
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
@endforeach
@endsection