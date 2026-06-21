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
                    <th>Customer</th>
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
                    $isOverdue = $check->check_date && $check->check_date->isPast() && ($check->check_amount - $check->encashed_amount) > 0;
                    $remainingCheck = $check->check_amount - $check->encashed_amount;
                @endphp
                <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                    <td><a href="{{ route('bills.show', $check->bill) }}">{{ $check->bill->bill_no ?? 'N/A' }}</a></td>
                    <td>{{ $check->bill->customer->name ?? 'N/A' }}</td>
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
                                    data-remaining="{{ $check->check_amount - $check->encashed_amount }}">
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
                    <td colspan="11" class="text-center py-3">No cheque payments found</td>
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
                            <label class="form-label text-muted small">Remaining</label>
                            <p class="fw-bold text-danger mb-0" id="modalRemaining">-</p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('encashModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function(event) {
        const btn = event.relatedTarget;
        const id = btn.dataset.id;
        const customer = btn.dataset.customer;
        const amount = btn.dataset.amount;
        const remaining = parseFloat(btn.dataset.remaining);
        document.getElementById('encashForm').action = '{{ route("dues.encash", "_ID_") }}'.replace('_ID_', id);
        document.getElementById('modalCustomer').textContent = customer;
        document.getElementById('modalOriginal').textContent = '\u09f3' + amount;
        document.getElementById('modalRemaining').textContent = '\u09f3' + remaining.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('encashAmount').value = remaining;
        document.getElementById('encashAmount').max = remaining;
    });
});
</script>
@endpush
@endsection
