@extends('layouts.admin')

@section('title', __('TT Payment Reports'))

@section('header', __('TT Payment Reports'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ __('TT Payment Reports') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            @if(auth()->user()->isAdmin())
            <div class="col-md-2">
                <label class="form-label small">{{ __('Branch / User') }}</label>
                <select name="user_id" class="form-select" onchange="this.form.submit()">
                    <option value="">{{ __('All Users') }}</option>
                    @foreach($users ?? [] as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label small">{{ __('Search') }}</label>
                <input type="text" name="search" class="form-control"
                       placeholder="{{ __('Bill no/customer...') }}"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="encashed" {{ request('status') == 'encashed' ? 'selected' : '' }}>{{ __('Encashed') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Bank') }}</label>
                <select name="bank" class="form-select">
                    <option value="">{{ __('All Banks') }}</option>
                    @foreach($banks ?? [] as $bank)
                    <option value="{{ $bank }}" {{ request('bank') == $bank ? 'selected' : '' }}>
                        {{ $bank }}
                    </option>
                    @endforeach
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
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('dues.tt-report') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total TT Amount') }}</h6>
                <h3 class="text-primary mb-0">{{ format_currency($totalTtAmount) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Payments') }}</h6>
                <h3 class="text-success mb-0">{{ $totalPayments }}</h3>
            </div>
        </div>
    </div>
</div>

<h5 class="mb-3">{{ __('TT Payments') }} ({{ $ttPayments->total() }})</h5>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Bill Date') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>
                        <a href="{{ route('dues.tt-report', ['sort' => 'tt_bank_name', 'direction' => request('sort') == 'tt_bank_name' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'bank', 'date_from', 'date_to', 'user_id', 'search')) }}" class="text-decoration-none">
                            {{ __('Bank') }} @if(request('sort') == 'tt_bank_name'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>{{ __('A/C No') }}</th>
                    <th>
                        <a href="{{ route('dues.tt-report', ['sort' => 'tt_amount', 'direction' => request('sort') == 'tt_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'bank', 'date_from', 'date_to', 'user_id', 'search')) }}" class="text-decoration-none">
                            {{ __('Amount') }} @if(request('sort') == 'tt_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('dues.tt-report', ['sort' => 'tt_date', 'direction' => request('sort') == 'tt_date' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'bank', 'date_from', 'date_to', 'user_id', 'search')) }}" class="text-decoration-none">
                            {{ __('TT Date') }} @if(request('sort') == 'tt_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('dues.tt-report', ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'bank', 'date_from', 'date_to', 'user_id', 'search')) }}" class="text-decoration-none">
                            {{ __('Status') }} @if(request('sort') == 'status'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ttPayments as $payment)
                <tr>
                    <td><a href="{{ route('bills.show', $payment->bill) }}">{{ $payment->bill->bill_no ?? 'N/A' }}</a></td>
                    <td>{{ $payment->bill->report_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ $payment->bill->customer->name ?? 'N/A' }}</td>
                    <td>{{ $payment->bill->customer->location ?? 'N/A' }}</td>
                    <td>{{ $payment->tt_bank_name ?? 'N/A' }}</td>
                    <td>{{ $payment->tt_account_no ?? 'N/A' }}</td>
                    <td class="fw-bold">{{ format_number($payment->tt_amount, 2) }}</td>
                    <td>{{ $payment->tt_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>
                        @if($payment->status === 'encashed')
                        <span class="badge bg-success">{{ __('Encashed') }}</span>
                        @else
                        <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('bills.show', $payment->bill) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-3">{{ __('No TT payments found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ttPayments->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $ttPayments->appends(request()->only('status', 'bank', 'date_from', 'date_to', 'search', 'sort', 'direction'))->links() !!}
    </div>
    @endif
</div>
@endsection
