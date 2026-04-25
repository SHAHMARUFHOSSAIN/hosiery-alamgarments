@extends('layouts.admin')

@section('title', 'Daily Due Report')

@section('header', 'Daily Due Report - ' . now()->format('M d, Y'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('dues.index') }}">Dues</a></li>
        <li class="breadcrumb-item active">Daily Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Due Today: {{ $todayDues->count() }}</h2>
    <div>
        <a href="{{ route('dues.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-warning text-dark py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-event"></i> 
                Total: {{ number_format($todayDues->sum('amount'), 2) }}
            </h5>
            <a href="{{ route('export.dues', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-sm btn-success">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>User</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayDues as $due)
                <tr>
                    <td>{{ $due->id }}</td>
                    <td><a href="{{ route('customers.show', $due->customer) }}" class="fw-semibold">{{ $due->customer->name ?? 'N/A' }}</a></td>
                    <td>{{ $due->customer->mobile ?? 'N/A' }}</td>
                    <td class="text-danger fw-bold fs-5">{{ number_format($due->amount, 2) }}</td>
                    <td><span class="badge bg-danger text-white">{{ $due->due_date->format('M d, Y') }}</span></td>
                    <td><span class="badge bg-secondary">{{ $due->creator->name ?? 'N/A' }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('dues.mark-paid', ['id' => $due->id]) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success py-0 px-2">Mark Paid</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-success">
                        <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                        <strong>No pending dues for today!</strong>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($todayDues->count() > 0)
            <tfoot class="table-dark">
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td class="text-danger fw-bold fs-5">{{ number_format($todayDues->sum('amount'), 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection