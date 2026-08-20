@extends('layouts.admin')

@section('title', __('My Dues Report'))

@section('header', __('My Dues Report'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user-reports.index') }}">{{ __('My Reports') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Dues') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Pending Dues') }}</h6>
                <h4 class="text-danger mb-0">{{ format_currency($totalPending ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Collected Dues') }}</h6>
                <h4 class="text-success mb-0">{{ format_currency($totalPaid ?? 0) }}</h4>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Collected Dues') }}</h6>
                <h4 class="text-success mb-0">{{ format_currency($totalPaid ?? 0) }}</h4>
            </div>
        </div>
    </div>


<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">{{ __('My Dues') }}</h2>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('user-reports.dues') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Bill Date') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Created') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dues as $due)
                <tr>
                    <td>
                        <strong>{{ $due->customer->name ?? __('Unknown') }}</strong>
                        @if($due->customer->mobile)
                        <br><small class="text-muted">{{ $due->customer->mobile }}</small>
                        @endif
                    </td>
                    <td>{{ $due->customer->location ?? 'N/A' }}</td>
                    <td>{{ $due->bill->bill_no ?? 'N/A' }}</td>
                    <td>{{ $due->bill->report_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td class="text-end text-danger fw-bold">{{ format_currency($due->amount) }}</td>
                    <td>{{ $due->due_date->format('M d, Y') }}</td>
                    <td>
                        @if($due->status == 'paid')
                        <span class="badge bg-success">{{ __('Paid') }}</span>
                        @else
                        <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                        @endif
                    </td>
                    <td>{{ $due->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-3">{{ __('No dues found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($dues->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $dues->links() !!}
    </div>
    @endif
</div>
@endsection
