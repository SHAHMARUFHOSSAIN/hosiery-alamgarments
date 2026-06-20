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
                        <a href="{{ route('bills.show', $check->bill) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($check->check_photo)
                        <a href="{{ route('storage.file', $check->check_photo) }}" target="_blank">
                            <img src="{{ route('storage.file', $check->check_photo) }}" alt="Cheque" class="img-thumbnail" style="width: 80px; height: 40px; object-fit: cover;">

                        <a href="{{ route('storage.file', $check->check_photo) }}" target="_blank">
                            <img src="{{ route('storage.file', $check->check_photo) }}" alt="Cheque" class="img-fluid border rounded" style="max-height: 150px;">
                        </a>
                    </div>
                    @endif
                    @if($check->encashed_amount > 0)
                    <div class="mb-3">
                        <strong>Total Encashed:</strong> <span class="text-success">৳{{ number_format($check->encashed_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="mb-3 alert alert-warning">
                        <strong>Remaining:</strong> <span class="text-danger fw-bold">৳{{ number_format($check->check_amount - $check->encashed_amount, 2) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="encash_amount" class="form-control" 
                                   max="{{ $check->check_amount - $check->encashed_amount }}" 
                                   value="{{ $check->check_amount - $check->encashed_amount }}" required>
                        </div>
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
