@extends('layouts.admin')

@section('title', 'Dues')
@section('header', 'Dues')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Customer or bill no..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('dues.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<h2 class="mb-4">All Dues ({{ $dues->count() }})</h2>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Customer</th>
                    <th>Bill</th>
                    <th>
                        <a href="{{ route('dues.index', ['sort' => 'original_amount', 'direction' => request('sort') == 'original_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'search')) }}" class="text-decoration-none">
                            Original @if(request('sort') == 'original_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Paid</th>
                    <th>
                        <a href="{{ route('dues.index', ['sort' => 'remaining_amount', 'direction' => request('sort') == 'remaining_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'search')) }}" class="text-decoration-none">
                            Remaining @if(request('sort') == 'remaining_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('dues.index', ['sort' => 'due_date', 'direction' => request('sort') == 'due_date' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'search')) }}" class="text-decoration-none">
                            Due Date @if(request('sort') == 'due_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('dues.index', ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'search')) }}" class="text-decoration-none">
                            Status @if(request('sort') == 'status'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dues as $due)
                <tr class="{{ $due->status == 'paid' ? '' : ($due->due_date->isPast() ? 'table-danger' : '') }}">
                    <td>
                        <strong>{{ $due->customer->name ?? 'Unknown' }}</strong>
                        @if($due->customer->mobile)
                        <br><small>{{ $due->customer->mobile }}</small>
                        @endif
                    </td>
                    <td>{{ $due->bill->bill_no ?? 'N/A' }}</td>
                    <td>৳{{ number_format($due->original_amount, 2) }}</td>
                    <td class="text-success fw-bold">৳{{ number_format($due->total_paid, 2) }}</td>
                    <td class="text-danger fw-bold">৳{{ number_format($due->remaining_amount, 2) }}</td>
                    <td>{{ $due->due_date->format('M d, Y') }}</td>
                    <td>
                        @if($due->status == 'paid')
                        <span class="badge bg-success">Paid</span>
                        @elseif($due->hasPartialPayments())
                        <span class="badge bg-info text-dark">Partial</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($due->status == 'pending')
                        <button type="button" class="btn btn-success btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#paymentModal{{ $due->id }}">
                            <i class="bi bi-credit-card"></i> Pay
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4"><strong>No dues found</strong></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($dues as $due)
@if($due->status == 'pending')
<div class="modal fade" id="paymentModal{{ $due->id }}" tabindex="-1">
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
