@extends('layouts.admin')

@section('title', 'Balance Report')

@section('header', 'Balance Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('main-balance.index') }}">Main Balance</a></li>
        <li class="breadcrumb-item active" aria-current="page">Report</li>
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
                <h6 class="text-white mb-1">Total Main Balance</h6>
                <h3 class="text-white mb-0">৳{{ number_format($totalMainBalance ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Branch-wise Balance</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-3">
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
                <a href="{{ route('main-balance.report') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchWise as $branch)
                    <tr>
                        <td>{{ $branch['name'] }}</td>
                        <td class="text-end text-success">৳{{ number_format($branch['credit'], 2) }}</td>
                        <td class="text-end text-danger">৳{{ number_format($branch['debit'], 2) }}</td>
                        <td class="text-end fw-bold">৳{{ number_format($branch['balance'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No data found</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th>Total</th>
                        <th class="text-end">৳{{ number_format($totalCredit, 2) }}</th>
                        <th class="text-end">৳{{ number_format($totalDebit, 2) }}</th>
                        <th class="text-end">৳{{ number_format($totalMainBalance, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Transaction History</h5>
    </div>
    <div class="card-body">
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
                        <th class="text-center">Voucher</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balances as $balance)
                    <tr>
                        <td>{{ $balance->created_at->format('d M Y h:i A') }}</td>
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
                        <td class="text-center">
                            <a href="{{ route('main-balance.voucher', $balance) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-receipt"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 7 : 6 }}" class="text-center text-muted py-4">No transactions found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection