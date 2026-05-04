@extends('layouts.admin')

@section('title', 'Sales Report')

@section('header', 'Sales Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active">Sales</li>
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
                       placeholder="Bill no/customer/shop..." 
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
                <label class="form-label small">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Sales Report ({{ $bills->total() }})</h5>
    <a href="{{ route('export.bills', request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="btn btn-success">
        <i class="bi bi-download"></i> Excel
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="bg-primary bg-opacity-10 p-3 rounded">
            <small class="text-muted">Total Sales</small>
            <h3 class="mb-0">{{ number_format($totalAmount, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-secondary bg-opacity-10 p-3 rounded">
            <small class="text-muted">Total Discount</small>
            <h3 class="mb-0">{{ number_format($totalDiscount, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-success bg-opacity-10 p-3 rounded">
            <small class="text-muted">Net Amount</small>
            <h3 class="mb-0">{{ number_format($totalAmount - $totalDiscount, 2) }}</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>
                        <a href="{{ route('reports.sales', ['sort' => 'bill_no', 'direction' => request('sort') == 'bill_no' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Bill No @if(request('sort') == 'bill_no'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Customer</th>
                    <th>Shop</th>
                    <th>
                        <a href="{{ route('reports.sales', ['sort' => 'bill_amount', 'direction' => request('sort') == 'bill_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Amount @if(request('sort') == 'bill_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Discount</th>
                    <th>
                        <a href="{{ route('reports.sales', ['sort' => 'net', 'direction' => request('sort') == 'net' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Net @if(request('sort') == 'net'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>User</th>
                    <th>
                        <a href="{{ route('reports.sales', ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            Date @if(request('sort') == 'created_at'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                <tr>
                    <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                    <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                    <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                    <td>{{ number_format($bill->bill_amount, 2) }}</td>
                    <td>{{ number_format($bill->discount, 2) }}</td>
                    <td class="fw-bold">{{ number_format($bill->bill_amount - $bill->discount, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ $bill->user->name ?? 'N/A' }}</span></td>
                    <td>{{ $bill->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-3">No bills found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bills->hasPages())
    <div class="mt-3 text-center">
        {!! str_replace('page-link', 'page-link btn btn-sm btn-outline-secondary', $bills->appends(request()->only('user_id', 'date_from', 'date_to', 'search', 'sort', 'direction'))->links()) !!}
    </div>
    @endif
</div>
@endsection