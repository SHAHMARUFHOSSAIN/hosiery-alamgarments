@extends('layouts.admin')

@section('title', $customer->name)

@section('header', 'Customer Details')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
        <li class="breadcrumb-item active">{{ $customer->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">{{ $customer->name }}</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('export.customer', [$customer, 'date_from' => request('date_from'), 'date_to' => request('date_to'), 'user_id' => request('user_id')]) }}" class="btn btn-success">
            <i class="bi bi-download"></i> Excel
        </a>
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-secondary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-cart text-info fs-2"></i>
                </div>
                <h3 class="mb-1">৳{{ number_format($totalBuy, 2) }}</h3>
                <p class="text-muted mb-0">Total Buy</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-currency-dollar text-danger fs-2"></i>
                </div>
                <h3 class="mb-1 text-danger">৳{{ number_format($totalDue, 2) }}</h3>
                <p class="text-muted mb-0">Total Due</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Customer Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>ID:</th>
                        <td>{{ $customer->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <th>Mobile:</th>
                        <td>{{ $customer->mobile ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Location:</th>
                        <td>{{ $customer->location ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Opening Balance:</th>
                        <td>
                            <span class="fw-bold text-{{ $customer->opening_balance > 0 ? 'danger' : 'success' }}">
                                ৳{{ number_format($customer->opening_balance, 2) }}
                            </span>
                            @if(auth()->user()->isAdmin())
                            <button type="button" class="btn btn-sm btn-outline-warning py-0 px-1 ms-1" 
                                    data-bs-toggle="modal" data-bs-target="#openingBalanceModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created By:</th>
                        <td><span class="badge bg-secondary">{{ $customer->creator->name ?? 'N/A' }}</span></td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $customer->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <form method="GET" class="row g-2 align-items-end">
                    @if(auth()->user()->isAdmin())
                    <div class="col-md-3">
                        <label class="form-label small">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Bills ({{ $bills->total() }})</h5>
                <a href="{{ route('bills.create') }}?customer_id={{ $customer->id }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> New Bill
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bill No</th>
                            <th>Shop Name</th>
                            <th>Amount</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                        <tr>
                            <td><a href="{{ route('bills.show', $bill) }}" class="fw-semibold">{{ $bill->bill_no }}</a>
                                @if($bill->edited_at)
                                    <span class="badge bg-warning text-dark ms-1" title="Edited by {{ $bill->editor?->name ?? 'Unknown' }}">Edited</span>
                                @endif
                            </td>
                            <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                            <td class="fw-bold">{{ number_format($bill->bill_amount, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ $bill->user->name ?? 'N/A' }}</span></td>
                            <td>{{ $bill->report_date->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-3">No bills found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($bills->hasPages())
            <div class="card-footer bg-white text-center">
                {!! $bills->appends(request()->only('date_from', 'date_to', 'user_id', 'sort', 'direction'))->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin())
<div class="modal fade" id="openingBalanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Opening Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('customers.opening-balance', $customer) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p class="text-muted">Set the opening balance for <strong>{{ $customer->name }}</strong> if they have any previous due amount.</p>
                    <div class="mb-3">
                        <label class="form-label">Opening Balance (৳)</label>
                        <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" 
                               value="{{ $customer->opening_balance }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection