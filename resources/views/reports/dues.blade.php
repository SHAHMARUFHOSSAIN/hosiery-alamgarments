@extends('layouts.admin')

@section('title', __('Dues Report'))

@section('header', __('Dues Report'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('Reports') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Dues') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">{{ __('Search') }}</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="{{ __('Customer or bill no...') }}" 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('User') }}</label>
                <select name="user_id" class="form-select">
                    <option value="">{{ __('All Users') }}</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>{{ __('Partial') }}</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Date From') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Date To') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h5 class="mb-0">{{ __('Dues Report') }} ({{ $dues->total() }})</h5>
    <a href="{{ route('export.dues', request()->only('user_id', 'status', 'date_from', 'date_to', 'search', 'sort', 'direction')) }}" class="btn btn-success">
        <i class="bi bi-download"></i> Excel
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="bg-danger bg-opacity-10 p-3 rounded">
            <small class="text-muted">{{ __('Pending Amount') }}</small>
            <h3 class="mb-0 text-danger">{{ format_number($totalAmount, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-warning bg-opacity-10 p-3 rounded">
            <small class="text-muted">{{ __('Total Records') }}</small>
            <h3 class="mb-0">{{ $dues->count() }}</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Mobile') }}</th>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Bill Date') }}</th>
                    <th>
                        <a href="{{ route('reports.dues', ['sort' => 'original_amount', 'direction' => request('sort') == 'original_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            {{ __('Original') }} @if(request('sort') == 'original_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>{{ __('Paid') }}</th>
                    <th>
                        <a href="{{ route('reports.dues', ['sort' => 'remaining_amount', 'direction' => request('sort') == 'remaining_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            {{ __('Remaining') }} @if(request('sort') == 'remaining_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('reports.dues', ['sort' => 'due_date', 'direction' => request('sort') == 'due_date' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            {{ __('Due Date') }} @if(request('sort') == 'due_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('reports.dues', ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'status', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            {{ __('Status') }} @if(request('sort') == 'status'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>{{ __('Created By') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dues as $due)
                <tr class="{{ $due->status == 'paid' ? '' : ($due->due_date->isPast() ? 'table-danger' : '') }}">
                    <td>{{ $due->id }}</td>
                    <td>{{ $due->customer->name ?? 'N/A' }}</td>
                    <td>{{ $due->customer->location ?? 'N/A' }}</td>
                    <td>{{ $due->customer->mobile ?? 'N/A' }}</td>
                    <td><a href="{{ route('bills.show', $due->bill) }}">{{ $due->bill->bill_no ?? 'N/A' }}</a></td>
                    <td>{{ $due->bill->report_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ format_number($due->original_amount, 2) }}</td>
                    <td class="text-success fw-bold">{{ format_number($due->total_paid, 2) }}</td>
                    <td class="text-danger fw-bold">{{ format_number($due->remaining_amount, 2) }}</td>
                    <td><span class="badge bg-{{ $due->due_date->isPast() && $due->status == 'pending' ? 'danger' : 'warning' }} text-dark">
                        {{ $due->due_date->format('M d, Y') }}
                    </span></td>
                    <td>
                        @if($due->status == 'paid')
                        <span class="badge bg-success">{{ __('Paid') }}</span>
                        @elseif($due->hasPartialPayments())
                        <span class="badge bg-info text-dark">{{ __('Partial') }}</span>
                        @else
                        <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $due->creator->name ?? 'N/A' }}</span></td>
                    <td>
                        @if($due->status == 'pending')
                        <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#reportPayModal{{ $due->id }}">
                            <i class="bi bi-credit-card"></i> {{ __('Pay') }}
                        </button>
                        @else
                        <span class="text-success"><i class="bi bi-check-circle"></i></span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="13" class="text-center py-3">{{ __('No dues found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($dues->hasPages())
    <div class="mt-3 text-center">
        {!! $dues->appends(request()->only('user_id', 'status', 'date_from', 'date_to', 'search', 'sort', 'direction'))->links() !!}
    </div>
    @endif
</div>
</div>

@foreach($dues as $due)
@if($due->status == 'pending')
<div class="modal fade" id="reportPayModal{{ $due->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Make Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dues.add-payment') }}">
                @csrf
                <input type="hidden" name="due_id" value="{{ $due->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>{{ __('Customer:') }}</strong> {{ $due->customer->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Bill:') }}</strong> {{ $due->bill->bill_no ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Original Amount:') }}</strong> {{ format_currency($due->original_amount) }}
                    </div>
                    @if($due->hasPartialPayments())
                    <div class="mb-3">
                        <strong>{{ __('Total Paid:') }}</strong> <span class="text-success">{{ format_currency($due->total_paid) }}</span>
                    </div>
                    @endif
                    <div class="mb-3 alert alert-warning">
                        <strong>{{ __('Remaining:') }}</strong> <span class="text-danger fw-bold">{{ format_currency($due->remaining_amount) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="payment_amount" class="form-control" 
                                   max="{{ $due->remaining_amount }}" value="{{ $due->remaining_amount }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Type') }} <span class="text-danger">*</span></label>
                        <select name="payment_type" class="form-select" required>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="check">{{ __('Cheque') }}</option>
                            <option value="mobile_banking">{{ __('Mobile Banking') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Next Due Date') }} <small class="text-muted">({{ __('if remaining balance') }})</small></label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Transaction ID') }} <small class="text-muted">({{ __('for reference') }})</small></label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="{{ __('e.g. TXN12345') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Note') }}</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="{{ __('Optional note...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> {{ __('Record Payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection
