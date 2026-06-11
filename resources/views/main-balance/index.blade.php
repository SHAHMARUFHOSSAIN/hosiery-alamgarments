@extends('layouts.admin')

@section('title', 'Main Balance')
@section('header', 'Main Balance')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-arrow-up-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total Credit</small>
                        <h4 class="mb-0 text-success fw-bold">৳{{ number_format($totalCredit ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="bi bi-arrow-down-circle text-danger fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total Debit</small>
                        <h4 class="mb-0 text-danger fw-bold">৳{{ number_format($totalDebit ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-wallet2 text-primary fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Current Balance</small>
                        <h4 class="mb-0 text-primary fw-bold">৳{{ number_format($mainBalance ?? 0, 2) }}</h4>
                    </div>
                </div>
                @if($selectedBranch)
                <small class="text-muted d-block mt-1">{{ $selectedBranch->name }}</small>
                @endif
            </div>
        </div>
    </div>
    @if(auth()->user()->isAdmin())
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-primary text-white">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-building fs-1"></i>
                <div>
                    <small class="text-white-50 text-uppercase fw-semibold">Overall Balance</small>
                    <h4 class="mb-0 fw-bold">৳{{ number_format($overallBalance ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>New Transaction</h5>
                <a href="{{ route('main-balance.report') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-file-earmark-text"></i> Balance Report
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('main-balance.store') }}">
                    @csrf
                    <div class="row g-3">
                        @if(auth()->user()->isAdmin())
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">Select Branch...</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Transaction Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Sales Revenue" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Party / Customer</label>
                            <input type="text" name="party_name" class="form-control" placeholder="Party or customer name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="typeCredit" value="credit" checked>
                                    <label class="form-check-label text-success fw-semibold" for="typeCredit">Credit</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="typeDebit" value="debit">
                                    <label class="form-check-label text-danger fw-semibold" for="typeDebit">Debit</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Invoice No.</label>
                            <input type="text" name="invoice_no" class="form-control" placeholder="INV-001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Reference</label>
                            <input type="text" name="reference" class="form-control" placeholder="REF-001">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Note / Description</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Optional notes about this transaction"></textarea>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg"></i> Record Transaction
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-info"></i>Account Summary</h5>
            </div>
            <div class="card-body">
                @if($selectedBranch)
                <div class="mb-3 p-3 bg-light rounded">
                    <small class="text-muted text-uppercase fw-semibold">Selected Branch</small>
                    <h5 class="mb-0">{{ $selectedBranch->name }}</h5>
                </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Opening Balance</td>
                                <td class="text-end fw-bold">৳0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Credit</td>
                                <td class="text-end text-success fw-bold">+ ৳{{ number_format($totalCredit ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Debit</td>
                                <td class="text-end text-danger fw-bold">- ৳{{ number_format($totalDebit ?? 0, 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-semibold">Current Balance</td>
                                <td class="text-end fw-bold fs-5 text-primary">৳{{ number_format($mainBalance ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Total Transactions</span>
                    <span class="badge bg-primary rounded-pill">{{ $balances->total() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0"><i class="bi bi-list-columns me-2 text-secondary"></i>Transaction History</h5>
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
                @if(auth()->user()->isAdmin())
                <select name="branch_id" class="form-select form-select-sm" style="width: 140px;">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @endif
                <select name="type" class="form-select form-select-sm" style="width: 110px;">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit</option>
                </select>
                <input type="text" name="search" class="form-control form-control-sm" style="width: 160px;" placeholder="Search..." value="{{ request('search') }}">
                <input type="date" name="date_from" class="form-control form-control-sm" style="width: 140px;" value="{{ request('date_from') }}">
                <input type="date" name="date_to" class="form-control form-control-sm" style="width: 140px;" value="{{ request('date_to') }}">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
                @if(request()->anyFilled(['branch_id', 'type', 'search', 'date_from', 'date_to']))
                <a href="{{ route('main-balance.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
                @endif
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    @if(auth()->user()->isAdmin())
                    <th>Branch</th>
                    @endif
                    <th>Name</th>
                    <th>Party</th>
                    <th>Invoice</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Balance</th>
                    <th>Reference</th>
                    <th>Note</th>
                    <th class="text-center">Voucher</th>
                </tr>
            </thead>
            <tbody>
                @forelse($balances as $balance)
                <tr>
                    <td class="text-muted small">{{ $balance->id }}</td>
                    <td class="small">{{ $balance->created_at->format('d M Y') }}<br><small class="text-muted">{{ $balance->created_at->format('h:i A') }}</small></td>
                    @if(auth()->user()->isAdmin())
                    <td><small>{{ $balance->branch->name ?? '-' }}</small></td>
                    @endif
                    <td class="fw-semibold">{{ $balance->name }}</td>
                    <td><small>{{ $balance->party_name ?? '-' }}</small></td>
                    <td><small>{{ $balance->invoice_no ?? '-' }}</small></td>
                    <td>
                        <span class="badge bg-{{ $balance->type === 'credit' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $balance->type === 'credit' ? 'success' : 'danger' }} fw-semibold">
                            <i class="bi bi-{{ $balance->type === 'credit' ? 'plus' : 'minus' }}"></i> {{ ucfirst($balance->type) }}
                        </span>
                    </td>
                    <td class="text-end fw-bold text-{{ $balance->type === 'credit' ? 'success' : 'danger' }}">
                        {{ $balance->type === 'credit' ? '+' : '-' }} ৳{{ number_format($balance->amount, 2) }}
                    </td>
                    <td class="text-end fw-semibold">৳{{ number_format($balance->balance, 2) }}</td>
                    <td><small>{{ $balance->reference ?? '-' }}</small></td>
                    <td><small class="text-muted">{{ $balance->note ?? '-' }}</small></td>
                    <td class="text-center">
                        <a href="{{ route('main-balance.voucher', $balance) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-receipt"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->isAdmin() ? 12 : 11 }}" class="text-center text-muted py-4">No transactions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($balances->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-center">
            {!! $balances->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection