@extends('layouts.admin')

@section('title', 'Dues Report')

@section('header', 'Dues Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active">Dues</li>
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
                       placeholder="Customer or bill no..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
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
                <label class="form-label small">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h5 class="mb-0">Dues Report ({{ $dues->total() }})</h5>
    <a href="{{ route('export.dues', request()->only('user_id', 'status', 'date_from', 'date_to', 'search', 'sort', 'direction')) }}" class="btn btn-success">
        <i class="bi bi-download"></i> Excel
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="bg-danger bg-opacity-10 p-3 rounded">
            <small class="text-muted">Pending Amount</small>
            <h3 class="mb-0 text-danger">{{ number_format($totalAmount, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-warning bg-opacity-10 p-3 rounded">
            <small class="text-muted">Total Records</small>
            <h3 class="mb-0">{{ $dues->count() }}</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Bill No</th>
                    <th>
                        <a href="{{ route('reports.dues', ['sort' => 'original_amount', 'direction' => request('sort') == 'original_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Original @if(request('sort') == 'original_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Paid</th>
                    <th>
                        <a href="{{ route('reports.dues', ['sort' => 'remaining_amount', 'direction' => request('sort') == 'remaining_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Remaining @if(request('sort') == 'remaining_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('reports.dues', ['sort' => 'due_date', 'direction' => request('sort') == 'due_date' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Due Date @if(request('sort') == 'due_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('reports.dues', ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Status @if(request('sort') == 'status'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Created By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dues as $due)
                <tr class="{{ $due->status == 'paid' ? '' : ($due->due_date->isPast() ? 'table-danger' : '') }}">
                    <td>{{ $due->id }}</td>
                    <td>{{ $due->customer->name ?? 'N/A' }}</td>
                    <td>{{ $due->customer->mobile ?? 'N/A' }}</td>
                    <td><a href="{{ route('bills.show', $due->bill) }}">{{ $due->bill->bill_no ?? 'N/A' }}</a></td>
                    <td>{{ number_format($due->original_amount, 2) }}</td>
                    <td class="text-success fw-bold">{{ number_format($due->total_paid, 2) }}</td>
                    <td class="text-danger fw-bold">{{ number_format($due->remaining_amount, 2) }}</td>
                    <td><span class="badge bg-{{ $due->due_date->isPast() && $due->status == 'pending' ? 'danger' : 'warning' }} text-dark">
                        {{ $due->due_date->format('M d, Y') }}
                    </span></td>
                    <td>
                        @if($due->status == 'paid')
                        <span class="badge bg-success">Paid</span>
                        @elseif($due->hasPartialPayments())
                        <span class="badge bg-info text-dark">Partial</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $due->creator->name ?? 'N/A' }}</span></td>
                    <td>
                        @if($due->status == 'pending')
                        <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#reportPayModal{{ $due->id }}">
                            <i class="bi bi-credit-card"></i> Pay
                        </button>
                        @else
                        <span class="text-success"><i class="bi bi-check-circle"></i></span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center py-3">No dues found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($dues->hasPages())
    <div class="mt-3 text-center">
        {!! $dues->appends(request()->only('user_id', 'status', 'date_from', 'date_to', 'search', 'sort', 'direction'))->links() !!}
    </div>
    @endif
</div>
</div>

@foreach($dues as $due)
@if($due->status == 'pending')
<div class="modal fade" id="reportPayModal{{ $due->id }}" tabindex="-1">
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
                        <strong>Bill:</strong> {{ $due->bill->bill_no ?? 'N/A' }}
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
@endif
@endforeach
@endsection