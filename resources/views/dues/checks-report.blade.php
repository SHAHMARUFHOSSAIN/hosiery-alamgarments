@extends('layouts.admin')

@section('title', 'Cheque Reports')

@section('header', 'Cheque Reports')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Cheque Reports</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
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
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="encashed" {{ request('status') == 'encashed' ? 'selected' : '' }}>Encashed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Bank</label>
                <select name="bank" class="form-select">
                    <option value="">All Banks</option>
                    @foreach($banks ?? [] as $bank)
                    <option value="{{ $bank }}" {{ request('bank') == $bank ? 'selected' : '' }}>
                        {{ $bank }}
                    </option>
                    @endforeach
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
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('dues.checks-report') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Cheque Amount</h6>
                <h3 class="text-primary mb-0">৳{{ number_format($totalCheckAmount, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Encashed</h6>
                <h3 class="text-success mb-0">৳{{ number_format($totalEncashedAmount, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Remaining</h6>
                <h3 class="text-danger mb-0">৳{{ number_format($totalRemainingAmount, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<h5 class="mb-3">Cheque Reports ({{ $allChecks->total() }})</h5>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bill No</th>
                    <th>Bill Date</th>
                    <th>Customer</th>
                    <th>Location</th>
                    <th>
                        <a href="{{ route('dues.checks-report', ['sort' => 'bank_name', 'direction' => request('sort') == 'bank_name' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'bank', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Bank @if(request('sort') == 'bank_name'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Cheque No</th>
                    <th>Original</th>
                    <th>
                        <a href="{{ route('dues.checks-report', ['sort' => 'encashed_amount', 'direction' => request('sort') == 'encashed_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'bank', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Encashed @if(request('sort') == 'encashed_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Remaining</th>
                    <th>
                        <a href="{{ route('dues.checks-report', ['sort' => 'check_date', 'direction' => request('sort') == 'check_date' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'bank', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Cheque Date @if(request('sort') == 'check_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Reminder</th>
                    <th>
                        <a href="{{ route('dues.checks-report', ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'bank', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Status @if(request('sort') == 'status'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allChecks as $check)
                @php
                    $remainingCheck = $check->remainingCheckAmount();
                    $isOverdue = $check->check_date && $check->check_date->isPast() && $remainingCheck > 0;
                @endphp
                <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                    <td><a href="{{ route('bills.show', $check->bill) }}">{{ $check->bill->bill_no ?? 'N/A' }}</a></td>
                    <td>{{ $check->bill->report_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ $check->bill->customer->name ?? 'N/A' }}</td>
                    <td>{{ $check->bill->customer->location ?? 'N/A' }}</td>
                    <td>{{ $check->bank_name ?? 'N/A' }}</td>
                    <td>{{ $check->check_no ?? 'N/A' }}</td>
                    <td>{{ number_format($check->check_amount, 2) }}</td>
                    <td class="text-success fw-bold">{{ number_format($check->encashed_amount, 2) }}</td>
                    <td class="text-danger fw-bold">{{ number_format($remainingCheck, 2) }}</td>
                    <td>
                        {{ $check->check_date?->format('M d, Y') ?? 'N/A' }}
                        @if($isOverdue)
                        <span class="badge bg-danger">Overdue</span>
                        @endif
                    </td>
                    <td>{{ $check->check_reminder_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>
                        @if($check->status === 'encashed')
                        <span class="badge bg-success">Encashed</span>
                        @elseif($check->partially_encashed)
                        <span class="badge bg-info text-dark">Partial</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1 align-items-center">
                            <a href="{{ route('bills.show', $check->bill) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($check->status !== 'encashed' && $check->check_amount > 0)
                            <button type="button" class="btn btn-sm btn-success py-0 px-2"
                                    data-bs-toggle="modal" data-bs-target="#encashModal"
                                    data-id="{{ $check->id }}"
                                    data-customer="{{ $check->bill->customer->name ?? 'N/A' }}"
                                    data-amount="{{ number_format($check->check_amount, 2) }}"
                                    data-remaining="{{ $remainingCheck }}"
                                    data-discount="{{ $check->total_encashment_discount }}">
                                <i class="bi bi-cash"></i> Encash
                            </button>
                            @endif
                            @if($check->check_photo)
                            <a href="{{ route('cheque.show', $check->check_photo) }}" target="_blank" title="View cheque">
                                <img src="{{ route('cheque.show', $check->check_photo) }}" alt="Cheque photo" class="rounded border" style="width: 60px; height: 32px; object-fit: cover;">
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center py-3">No cheque payments found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($allChecks->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $allChecks->links() !!}
    </div>
    @endif
</div>

@if(isset($dueChecks) && $dueChecks->total() > 0)
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-bank text-warning"></i> Due Payment Cheques ({{ $dueChecks->total() }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Customer</th>
                    <th>Location</th>
                    <th>Bank</th>
                    <th>Cheque No</th>
                    <th>Amount</th>
                    <th>Cheque Date</th>
                    <th>Reminder</th>
                    <th>Photo</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dueChecks as $dueCheck)
                @php $remainingEncash = ($dueCheck->check_amount ?? $dueCheck->amount) - $dueCheck->encashed_amount; @endphp
                <tr>
                    <td>{{ $dueCheck->due->customer->name ?? 'N/A' }}</td>
                    <td>{{ $dueCheck->due->customer->location ?? 'N/A' }}</td>
                    <td>{{ $dueCheck->bank_name ?? 'N/A' }}</td>
                    <td>{{ $dueCheck->check_no ?? 'N/A' }}</td>
                    <td>৳{{ number_format($dueCheck->check_amount ?? $dueCheck->amount, 2) }}</td>
                    <td>{{ $dueCheck->check_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ $dueCheck->check_reminder_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>
                        @if($dueCheck->check_photo)
                        <a href="{{ route('cheque.show', $dueCheck->check_photo) }}" target="_blank">
                            <img src="{{ route('cheque.show', $dueCheck->check_photo) }}" alt="Cheque" class="rounded border" style="width: 60px; height: 32px; object-fit: cover;">
                        </a>
                        @else
                        —
                        @endif
                    </td>
                    <td>
                        @if($dueCheck->status === 'encashed')
                        <span class="badge bg-success">Encashed</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($dueCheck->status !== 'encashed' && $remainingEncash > 0)
                        <button type="button" class="btn btn-sm btn-success py-0 px-2"
                                data-bs-toggle="modal" data-bs-target="#dueEncashModal"
                                data-id="{{ $dueCheck->id }}"
                                data-customer="{{ $dueCheck->due->customer->name ?? 'N/A' }}"
                                data-amount="{{ number_format($dueCheck->check_amount ?? $dueCheck->amount, 2) }}"
                                data-remaining="{{ $remainingEncash }}"
                                data-discount="{{ $dueCheck->discount }}">
                            <i class="bi bi-cash"></i> Encash
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($dueChecks->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $dueChecks->links() !!}
    </div>
    @endif
</div>
@endif

@if(isset($prevDueChecks) && $prevDueChecks->total() > 0)
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-bank text-warning"></i> Previous Due Payment Cheques ({{ $prevDueChecks->total() }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Customer</th>
                    <th>Location</th>
                    <th>Bank</th>
                    <th>Cheque No</th>
                    <th>Amount</th>
                    <th>Cheque Date</th>
                    <th>Reminder</th>
                    <th>Photo</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prevDueChecks as $prevDueCheck)
                @php $remainingEncash = ($prevDueCheck->check_amount ?? $prevDueCheck->amount) - $prevDueCheck->encashed_amount; @endphp
                <tr>
                    <td>{{ $prevDueCheck->previousDue->customer->name ?? 'N/A' }}</td>
                    <td>{{ $prevDueCheck->previousDue->customer->location ?? 'N/A' }}</td>
                    <td>{{ $prevDueCheck->bank_name ?? 'N/A' }}</td>
                    <td>{{ $prevDueCheck->check_no ?? 'N/A' }}</td>
                    <td>৳{{ number_format($prevDueCheck->check_amount ?? $prevDueCheck->amount, 2) }}</td>
                    <td>{{ $prevDueCheck->check_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ $prevDueCheck->check_reminder_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>
                        @if($prevDueCheck->check_photo)
                        <a href="{{ route('cheque.show', $prevDueCheck->check_photo) }}" target="_blank">
                            <img src="{{ route('cheque.show', $prevDueCheck->check_photo) }}" alt="Cheque" class="rounded border" style="width: 60px; height: 32px; object-fit: cover;">
                        </a>
                        @else
                        —
                        @endif
                    </td>
                    <td>
                        @if($prevDueCheck->status === 'encashed')
                        <span class="badge bg-success">Encashed</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($prevDueCheck->status !== 'encashed' && $remainingEncash > 0)
                        <button type="button" class="btn btn-sm btn-success py-0 px-2"
                                data-bs-toggle="modal" data-bs-target="#prevDueEncashModal"
                                data-id="{{ $prevDueCheck->id }}"
                                data-customer="{{ $prevDueCheck->previousDue->customer->name ?? 'N/A' }}"
                                data-amount="{{ number_format($prevDueCheck->check_amount ?? $prevDueCheck->amount, 2) }}"
                                data-remaining="{{ $remainingEncash }}"
                                data-discount="{{ $prevDueCheck->discount }}">
                            <i class="bi bi-cash"></i> Encash
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($prevDueChecks->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $prevDueChecks->links() !!}
    </div>
    @endif
</div>
@endif

<div class="modal fade" id="encashModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="encashForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Make Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Customer</label>
                            <p class="fw-bold mb-0" id="modalCustomer">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Original Amount</label>
                            <p class="fw-bold mb-0" id="modalOriginal">-</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remaining</label>
                            <p class="fw-bold text-danger mb-0" id="modalRemaining">-</p>
                        </div>
                        <div class="col-12" id="existingDiscountRow" style="display:none">
                            <label class="form-label text-muted small">Existing Discount</label>
                            <p class="fw-bold text-warning mb-0" id="modalExistingDiscount">-</p>
                        </div>
                        <hr class="my-2">
                        <div class="col-12">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="encash_amount" id="encashAmount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Discount <small class="text-muted">(optional, auto subtracts)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="discount" id="encashDiscount"
                                       class="form-control" value="0" min="0">
                            </div>
                            <div id="encashDiscountInfo" class="form-text mt-1">
                                Total discount: ৳<span id="encashTotalDiscDisplay">0.00</span> | Remaining after: ৳<span id="encashRemainDisplay">0.00</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="check">Cheque</option>
                                <option value="mobile_banking">Mobile Banking</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Due Date <small class="text-muted">(if remaining balance)</small></label>
                            <input type="date" name="next_due_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transaction ID <small class="text-muted">(for reference)</small></label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN12345">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                        </div>
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

{{-- Due Payment Encash Modal --}}
<div class="modal fade" id="dueEncashModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="dueEncashForm" action="{{ route('dues.due-encash') }}">
                @csrf
                <input type="hidden" name="due_payment_id" id="dueEncashId">
                <div class="modal-header">
                    <h5 class="modal-title">Encash Due Cheque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Customer</label>
                            <p class="fw-bold mb-0" id="dueEncashCustomer">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Original Amount</label>
                            <p class="fw-bold mb-0" id="dueEncashOriginal">-</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remaining</label>
                            <p class="fw-bold text-danger mb-0" id="dueEncashRemaining">-</p>
                        </div>
                        <hr class="my-2">
                        <div class="col-12">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="encash_amount" id="dueEncashAmount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Discount <small class="text-muted">(optional, auto subtracts)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="discount" id="dueEncashDiscount"
                                       class="form-control" value="0" min="0">
                            </div>
                            <div id="dueEncashDiscountInfo" class="form-text mt-1">
                                Total discount: ৳<span id="dueEncashTotalDiscDisplay">0.00</span> | Remaining after: ৳<span id="dueEncashRemainDisplay">0.00</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="check">Cheque</option>
                                <option value="mobile_banking">Mobile Banking</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transaction ID <small class="text-muted">(for reference)</small></label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN12345">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-cash"></i> Encash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Previous Due Payment Encash Modal --}}
<div class="modal fade" id="prevDueEncashModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="prevDueEncashForm" action="{{ route('dues.prev-due-encash') }}">
                @csrf
                <input type="hidden" name="prev_due_payment_id" id="prevDueEncashId">
                <div class="modal-header">
                    <h5 class="modal-title">Encash Previous Due Cheque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Customer</label>
                            <p class="fw-bold mb-0" id="prevDueEncashCustomer">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Original Amount</label>
                            <p class="fw-bold mb-0" id="prevDueEncashOriginal">-</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remaining</label>
                            <p class="fw-bold text-danger mb-0" id="prevDueEncashRemaining">-</p>
                        </div>
                        <hr class="my-2">
                        <div class="col-12">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="encash_amount" id="prevDueEncashAmount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Discount <small class="text-muted">(optional, auto subtracts)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="discount" id="prevDueEncashDiscount"
                                       class="form-control" value="0" min="0">
                            </div>
                            <div id="prevDueEncashDiscountInfo" class="form-text mt-1">
                                Total discount: ৳<span id="prevDueEncashTotalDiscDisplay">0.00</span> | Remaining after: ৳<span id="prevDueEncashRemainDisplay">0.00</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="check">Cheque</option>
                                <option value="mobile_banking">Mobile Banking</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transaction ID <small class="text-muted">(for reference)</small></label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN12345">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-cash"></i> Encash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateEncashDiscount() {
    var discountEl = document.getElementById('encashDiscount');
    var amountEl = document.getElementById('encashAmount');
    var remainDisplay = document.getElementById('encashRemainDisplay');
    var totalDiscDisplay = document.getElementById('encashTotalDiscDisplay');
    if (!discountEl || !amountEl || !remainDisplay) return;
    var remaining = parseFloat(discountEl.getAttribute('data-remaining')) || 0;
    var existingDiscount = parseFloat(discountEl.getAttribute('data-existing-discount')) || 0;
    var discount = parseFloat(discountEl.value) || 0;
    var payVal = parseFloat(amountEl.value) || 0;
    var effectiveRemaining = Math.max(0, remaining - discount);
    var totalDiscount = existingDiscount + discount;
    if (totalDiscDisplay) totalDiscDisplay.textContent = totalDiscount.toFixed(2);
    remainDisplay.textContent = effectiveRemaining.toFixed(2);
    amountEl.max = effectiveRemaining;
    if (payVal > effectiveRemaining) {
        amountEl.value = effectiveRemaining;
    }
}
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('encashModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function(event) {
        var btn = event.relatedTarget;
        var id = btn.dataset.id;
        var customer = btn.dataset.customer;
        var amount = btn.dataset.amount;
        var remaining = parseFloat(btn.dataset.remaining);
        var existingDiscount = parseFloat(btn.dataset.discount) || 0;
        document.getElementById('encashForm').action = '{{ route("dues.encash", "_ID_") }}'.replace('_ID_', id);
        document.getElementById('modalCustomer').textContent = customer;
        document.getElementById('modalOriginal').textContent = '\u09f3' + amount;
        document.getElementById('modalRemaining').textContent = '\u09f3' + remaining.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        var existingDiscRow = document.getElementById('existingDiscountRow');
        var modalExistingDisc = document.getElementById('modalExistingDiscount');
        if (existingDiscount > 0) {
            existingDiscRow.style.display = '';
            modalExistingDisc.textContent = '\u09f3' + existingDiscount.toFixed(2);
        } else {
            existingDiscRow.style.display = 'none';
        }
        var discountEl = document.getElementById('encashDiscount');
        discountEl.value = 0;
        discountEl.max = remaining;
        discountEl.setAttribute('data-remaining', remaining);
        discountEl.setAttribute('data-existing-discount', existingDiscount);
        var amountEl = document.getElementById('encashAmount');
        amountEl.value = remaining;
        amountEl.max = remaining;
        updateEncashDiscount();
    });
    document.getElementById('encashDiscount')?.addEventListener('input', updateEncashDiscount);
    document.getElementById('encashAmount')?.addEventListener('input', updateEncashDiscount);
});

function updateDueEncashDiscount() {
    var discountEl = document.getElementById('dueEncashDiscount');
    var amountEl = document.getElementById('dueEncashAmount');
    var remainDisplay = document.getElementById('dueEncashRemainDisplay');
    var totalDiscDisplay = document.getElementById('dueEncashTotalDiscDisplay');
    if (!discountEl || !amountEl || !remainDisplay) return;
    var remaining = parseFloat(discountEl.getAttribute('data-remaining')) || 0;
    var existingDiscount = parseFloat(discountEl.getAttribute('data-existing-discount')) || 0;
    var discount = parseFloat(discountEl.value) || 0;
    var payVal = parseFloat(amountEl.value) || 0;
    var effectiveRemaining = Math.max(0, remaining - discount);
    var totalDiscount = existingDiscount + discount;
    if (totalDiscDisplay) totalDiscDisplay.textContent = totalDiscount.toFixed(2);
    remainDisplay.textContent = effectiveRemaining.toFixed(2);
    amountEl.max = effectiveRemaining;
    if (payVal > effectiveRemaining) {
        amountEl.value = effectiveRemaining;
    }
}
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('dueEncashModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function(event) {
        var btn = event.relatedTarget;
        var id = btn.dataset.id;
        var customer = btn.dataset.customer;
        var amount = btn.dataset.amount;
        var remaining = parseFloat(btn.dataset.remaining);
        var existingDiscount = parseFloat(btn.dataset.discount) || 0;
        document.getElementById('dueEncashId').value = id;
        document.getElementById('dueEncashCustomer').textContent = customer;
        document.getElementById('dueEncashOriginal').textContent = '\u09f3' + amount;
        document.getElementById('dueEncashRemaining').textContent = '\u09f3' + remaining.toFixed(2);
        var discountEl = document.getElementById('dueEncashDiscount');
        discountEl.value = 0;
        discountEl.max = remaining;
        discountEl.setAttribute('data-remaining', remaining);
        discountEl.setAttribute('data-existing-discount', existingDiscount);
        var amountEl = document.getElementById('dueEncashAmount');
        amountEl.value = remaining;
        amountEl.max = remaining;
        updateDueEncashDiscount();
    });
    document.getElementById('dueEncashDiscount')?.addEventListener('input', updateDueEncashDiscount);
    document.getElementById('dueEncashAmount')?.addEventListener('input', updateDueEncashDiscount);
});

function updatePrevDueEncashDiscount() {
    var discountEl = document.getElementById('prevDueEncashDiscount');
    var amountEl = document.getElementById('prevDueEncashAmount');
    var remainDisplay = document.getElementById('prevDueEncashRemainDisplay');
    var totalDiscDisplay = document.getElementById('prevDueEncashTotalDiscDisplay');
    if (!discountEl || !amountEl || !remainDisplay) return;
    var remaining = parseFloat(discountEl.getAttribute('data-remaining')) || 0;
    var existingDiscount = parseFloat(discountEl.getAttribute('data-existing-discount')) || 0;
    var discount = parseFloat(discountEl.value) || 0;
    var payVal = parseFloat(amountEl.value) || 0;
    var effectiveRemaining = Math.max(0, remaining - discount);
    var totalDiscount = existingDiscount + discount;
    if (totalDiscDisplay) totalDiscDisplay.textContent = totalDiscount.toFixed(2);
    remainDisplay.textContent = effectiveRemaining.toFixed(2);
    amountEl.max = effectiveRemaining;
    if (payVal > effectiveRemaining) {
        amountEl.value = effectiveRemaining;
    }
}
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('prevDueEncashModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function(event) {
        var btn = event.relatedTarget;
        var id = btn.dataset.id;
        var customer = btn.dataset.customer;
        var amount = btn.dataset.amount;
        var remaining = parseFloat(btn.dataset.remaining);
        var existingDiscount = parseFloat(btn.dataset.discount) || 0;
        document.getElementById('prevDueEncashId').value = id;
        document.getElementById('prevDueEncashCustomer').textContent = customer;
        document.getElementById('prevDueEncashOriginal').textContent = '\u09f3' + amount;
        document.getElementById('prevDueEncashRemaining').textContent = '\u09f3' + remaining.toFixed(2);
        var discountEl = document.getElementById('prevDueEncashDiscount');
        discountEl.value = 0;
        discountEl.max = Math.max(0, remaining - existingDiscount);
        discountEl.setAttribute('data-remaining', remaining);
        discountEl.setAttribute('data-existing-discount', existingDiscount);
        var amountEl = document.getElementById('prevDueEncashAmount');
        amountEl.value = remaining - existingDiscount;
        amountEl.max = remaining - existingDiscount;
        updatePrevDueEncashDiscount();
    });
    document.getElementById('prevDueEncashDiscount')?.addEventListener('input', updatePrevDueEncashDiscount);
    document.getElementById('prevDueEncashAmount')?.addEventListener('input', updatePrevDueEncashDiscount);
});
</script>
@endpush
@endsection
