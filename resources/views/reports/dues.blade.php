@extends('layouts.admin')

@section('title', 'Dues Report')

@section('header', 'Dues Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active">Dues</li>
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
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('reports.dues') }}" class="btn btn-outline-secondary">Clear</a>
                <a href="{{ route('export.dues', request()->only('user_id', 'status', 'date_from', 'date_to')) }}" class="btn btn-success">
                    <i class="bi bi-download"></i> Excel
                </a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="bg-danger bg-opacity-10 p-3 rounded">
                    <small class="text-muted">Pending Amount</small>
                    <h3 class="mb-0 text-danger">{{ number_format($totalAmount, 2) }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-warning bg-opacity-10 p-3 rounded">
                    <small class="text-muted">Total Records</small>
                    <h3 class="mb-0">{{ $dues->count() }}</h3>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Mobile</th>
                        <th>Bill No</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dues as $due)
                    <tr>
                        <td>{{ $due->id }}</td>
                        <td>{{ $due->customer->name ?? 'N/A' }}</td>
                        <td>{{ $due->customer->mobile ?? 'N/A' }}</td>
                        <td><a href="{{ route('bills.show', $due->bill) }}">{{ $due->bill->bill_no ?? 'N/A' }}</a></td>
                        <td class="text-danger fw-bold">{{ number_format($due->amount, 2) }}</td>
                        <td><span class="badge bg-{{ $due->due_date->isPast() && $due->status == 'pending' ? 'danger' : 'warning' }} text-dark">
                            {{ $due->due_date->format('M d, Y') }}
                        </span></td>
                        <td><span class="badge bg-{{ $due->status == 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($due->status) }}
                        </span></td>
                        <td><span class="badge bg-secondary">{{ $due->creator->name ?? 'N/A' }}</span></td>
                        <td>
                            @if($due->status == 'pending')
                            <form method="POST" action="{{ route('dues.mark-paid', $due) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success py-0 px-2">Paid</button>
                            </form>
                            @else
                            <span class="text-success"><i class="bi bi-check-circle"></i></span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-3">No dues found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $dues->links() }}</div>
    </div>
</div>
@endsection