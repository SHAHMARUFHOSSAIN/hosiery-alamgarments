@extends('layouts.admin')

@section('title', 'Previous Due Report')

@section('header', 'Previous Due Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
        <li class="breadcrumb-item active">Previous Dues</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Customer..." value="{{ request('search') }}">
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
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
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
            <div class="col-md-1">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h5 class="mb-0">Previous Due Report ({{ $previousDues->total() }})</h5>
    <a href="{{ route('export.previous-dues', request()->only('user_id', 'status', 'date_from', 'date_to', 'search')) }}" class="btn btn-success">
        <i class="bi bi-download"></i> Excel
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="bg-dark bg-opacity-10 p-3 rounded">
            <small class="text-muted">Total Original Amount</small>
            <h3 class="mb-0">৳{{ number_format($totalAmount, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-6">
        <div class="bg-danger bg-opacity-10 p-3 rounded">
            <small class="text-muted">Total Remaining Pending</small>
            <h3 class="mb-0 text-danger">৳{{ number_format($totalPending, 2) }}</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Original</th>
                    <th>Paid</th>
                    <th>Remaining</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($previousDues as $pd)
                <tr>
                    <td>{{ $pd->id }}</td>
                    <td>{{ $pd->customer->name ?? 'N/A' }}</td>
                    <td>{{ $pd->customer->mobile ?? 'N/A' }}</td>
                    <td>৳{{ number_format($pd->original_amount, 2) }}</td>
                    <td class="text-success fw-bold">৳{{ number_format($pd->total_paid, 2) }}</td>
                    <td class="text-danger fw-bold">৳{{ number_format($pd->remaining_amount, 2) }}</td>
                    <td>
                        @if($pd->status == 'paid')
                        <span class="badge bg-success">Paid</span>
                        @elseif($pd->hasPartialPayments())
                        <span class="badge bg-info text-dark">Partial</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $pd->creator->name ?? 'N/A' }}</span></td>
                    <td><small>{{ $pd->created_at->format('M d, Y') }}</small></td>
                    <td>
                        <a href="{{ route('previous-dues.show', $pd) }}" class="btn btn-sm btn-info py-0 px-2">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-3">No previous dues found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($previousDues->hasPages())
    <div class="mt-3 text-center">
        {!! $previousDues->appends(request()->only('user_id', 'status', 'date_from', 'date_to', 'search', 'sort', 'direction'))->links() !!}
    </div>
    @endif
</div>
@endsection
