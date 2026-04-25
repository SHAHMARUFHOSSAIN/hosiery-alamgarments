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
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">{{ $customer->name }}</h2>
    <div>
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-secondary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
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
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Bills</h5>
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
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->bills as $bill)
                        <tr>
                            <td><a href="{{ route('bills.show', $bill) }}" class="fw-semibold">{{ $bill->bill_no }}</a></td>
                            <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                            <td class="fw-bold">{{ number_format($bill->bill_amount, 2) }}</td>
                            <td>{{ $bill->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3">No bills found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection