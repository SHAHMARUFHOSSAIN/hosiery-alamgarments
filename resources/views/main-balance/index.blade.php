@extends('layouts.admin')

@section('title', 'Main Balance')

@section('header', 'Main Balance')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Credit</h6>
                <h3 class="text-success mb-0">৳{{ number_format($totalCredit ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Debit</h6>
                <h3 class="text-danger mb-0">৳{{ number_format($totalDebit ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Current Balance</h6>
                <h3 class="text-primary mb-0">৳{{ number_format($mainBalance ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-wallet2 text-primary fs-2"></i>
            </div>
            <h5>Balance Report</h5>
            <p class="text-muted">View main balance with branch-wise calculation</p>
            <a href="{{ route('main-balance.report') }}" class="btn btn-primary">View Report</a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Transaction</h5>
            <a href="{{ route('main-balance.report') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-text"></i> Full Report
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('main-balance.store') }}">
            @csrf
            <div class="row g-3">
                @if(auth()->user()->isAdmin())
                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter name" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="credit">Credit (+)</option>
                        <option value="debit">Debit (-)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Note</label>
                    <input type="text" name="note" class="form-control" placeholder="Optional note">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Add Transaction</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Transaction History</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-3">
            @if(auth()->user()->isAdmin())
            <div class="col-md-3">
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
                <a href="{{ route('main-balance.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        @if(auth()->user()->isAdmin())
                        <th>Branch</th>
                        @endif
                        <th>Name</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balances as $balance)
                    <tr>
                        <td>{{ $balance->created_at->format('d M Y') }}</td>
                        @if(auth()->user()->isAdmin())
                        <td>{{ $balance->branch->name ?? '-' }}</td>
                        @endif
                        <td>{{ $balance->name }}</td>
                        <td>
                            <span class="badge bg-{{ $balance->type === 'credit' ? 'success' : 'danger' }}">
                                {{ ucfirst($balance->type) }}
                            </span>
                        </td>
                        <td class="text-end">৳{{ number_format($balance->amount, 2) }}</td>
                        <td>{{ $balance->note ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="text-center text-muted py-4">No transactions found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($balances->hasPages())
        <div class="card-footer bg-white text-center">
            {!! str_replace('page-link', 'page-link btn btn-sm btn-outline-secondary', $balances->links()) !!}
        </div>
        @endif
    </div>
</div>
@endsection