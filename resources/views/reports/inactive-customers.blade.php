@extends('layouts.admin')

@section('title', 'Inactive Customers')

@section('header', 'Inactive Customers')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active">Inactive Customers</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inactive Customers ({{ $customers->total() }})</h5>
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('reports.inactive-customers') }}" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                        </form>
                        <a href="{{ route('export.inactive-customers') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-download"></i> Export
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Location</th>
                                <th>Last Bill</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->mobile ?? 'N/A' }}</td>
                                <td>{{ $customer->location ?? 'N/A' }}</td>
                                <td>{{ $customer->bills->first()?->created_at?->format('Y-m-d') ?? 'N/A' }}</td>
                                <td>{{ $customer->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No inactive customers found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($customers->hasPages())
            <div class="card-footer bg-white text-center">
                {!! $customers->appends(request()->only('search'))->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection