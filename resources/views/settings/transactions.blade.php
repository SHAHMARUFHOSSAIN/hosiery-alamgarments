@extends('layouts.admin')

@section('title', 'All Transactions')

@section('header', 'All Transactions')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
        <li class="breadcrumb-item active">Transactions</li>
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
                <h6 class="text-white mb-1">Total Balance</h6>
                <h3 class="text-white mb-0">৳{{ number_format($totalBalance ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('settings.transactions') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Branch</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                    <th>Note</th>
                    <th class="text-center">Voucher</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $balance)
                <tr>
                    <td>{{ $balance->created_at->format('d M Y h:i A') }}</td>
                    <td>{{ $balance->branch->name ?? '-' }}</td>
                    <td>{{ $balance->name }}</td>
                    <td>
                        <span class="badge bg-{{ $balance->type === 'credit' ? 'success' : 'danger' }}">
                            {{ ucfirst($balance->type) }}
                        </span>
                    </td>
                    <td class="text-end">৳{{ number_format($balance->amount, 2) }}</td>
                    <td>{{ $balance->note ?? '-' }}</td>
                    <td class="text-center">
                        <a href="{{ route('main-balance.voucher', $balance) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-receipt"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4">No transactions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="px-3 pb-3">
        {!! $transactions->links() !!}
    </div>
    @endif
</div>
@endsection