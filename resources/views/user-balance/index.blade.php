@extends('layouts.admin')

@section('title', 'My Balance')

@section('header', 'My Balance')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">My Balance</li>
    </ol>
</nav>
@endsection

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
        <div class="card border-0 shadow-sm bg-primary">
            <div class="card-body text-center">
                <h6 class="text-white mb-1">My Balance</h6>
                <h3 class="text-white mb-0">৳{{ number_format($mainBalance ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Add Transaction</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('user-balance.store') }}">
            @csrf
            <div class="row g-3">
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
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Transactions</h5>
            <form method="GET" class="d-flex gap-2">
                <select name="type" class="form-select form-select-sm" style="width: 120px;">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit</option>
                </select>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" style="width: 140px;">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" style="width: 140px;">
                <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
                <a href="{{ route('user-balance.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
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
                        <td colspan="5" class="text-center text-muted py-4">No transactions found</td>
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