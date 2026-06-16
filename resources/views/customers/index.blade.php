@extends('layouts.admin')

@section('title', 'Customers')

@section('header', 'Customers')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Customers</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">Customers</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('export.customers', request()->only('search', 'user_id', 'location')) }}" class="btn btn-success">
            <i class="bi bi-download"></i> Excel
        </a>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Add Customer
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-people text-primary fs-2"></i>
                </div>
                <h3 class="mb-1">{{ number_format($totalCustomers) }}</h3>
                <p class="text-muted mb-0">Total Customers</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by name or mobile..." 
                       value="{{ request('search') }}">
            </div>
            @if(auth()->user()->isAdmin())
            <div class="col-md-3">
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users ?? [] as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3">
                <select name="location" class="form-select">
                    <option value="">All Locations</option>
                    @foreach($locations ?? [] as $location)
                    <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                        {{ $location }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Location</th>
                    <th>Created By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td><a href="{{ route('customers.show', $customer) }}" class="fw-semibold">{{ $customer->name }}</a></td>
                    <td>{{ $customer->mobile ?? 'N/A' }}</td>
                    <td>{{ $customer->location ?? 'N/A' }}</td>
                    <td><span class="badge bg-secondary">{{ $customer->creator->name ?? 'N/A' }}</span></td>
                    <td>{{ $customer->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" 
                              class="d-inline" onsubmit="return confirm('Delete this customer?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3">No customers found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $customers->links() !!}
    </div>
    @endif
</div>
@endsection