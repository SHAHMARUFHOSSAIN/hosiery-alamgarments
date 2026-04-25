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
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="Date From">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="Date To">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">Clear</a>
                <a href="{{ route('export.bills', request()->only('user_id', 'date_from', 'date_to')) }}" class="btn btn-success">
                    <i class="bi bi-download"></i> Excel
                </a>
            </div>
        </form>
    </div>
    <div class="card-body">
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

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Bill No</th>
                        <th>Customer</th>
                        <th>Shop</th>
                        <th>Amount</th>
                        <th>Discount</th>
                        <th>Net</th>
                        <th>User</th>
                        <th>Date</th>
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
        <div class="mt-3">{{ $bills->links() }}</div>
    </div>
</div>
@endsection