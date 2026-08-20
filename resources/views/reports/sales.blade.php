@extends('layouts.admin')

@section('title', __('Sales Report'))

@section('header', __('Sales Report'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('Reports') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Sales') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">{{ __('Search') }}</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="{{ __('Bill no/customer/shop...') }}" 
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
                <label class="form-label small">{{ __('Date From') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Date To') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h5 class="mb-0">{{ __('Sales Report') }} ({{ $bills->total() }})</h5>
    <a href="{{ route('export.bills', request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="btn btn-success">
        <i class="bi bi-download"></i> Excel
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="bg-primary bg-opacity-10 p-3 rounded">
            <small class="text-muted">{{ __('Total Sales') }}</small>
            <h3 class="mb-0">{{ format_number($totalAmount, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-secondary bg-opacity-10 p-3 rounded">
            <small class="text-muted">{{ __('Total Discount') }}</small>
            <h3 class="mb-0">{{ format_number($totalDiscount, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-success bg-opacity-10 p-3 rounded">
            <small class="text-muted">{{ __('Net Amount') }}</small>
            <h3 class="mb-0">{{ format_number($totalAmount - $totalDiscount, 2) }}</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>
                        <a href="{{ route('reports.sales', ['sort' => 'bill_no', 'direction' => request('sort') == 'bill_no' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            {{ __('Bill No') }} @if(request('sort') == 'bill_no'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Shop') }}</th>
                    <th>
                        <a href="{{ route('reports.sales', ['sort' => 'bill_amount', 'direction' => request('sort') == 'bill_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            {{ __('Amount') }} @if(request('sort') == 'bill_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>{{ __('Discount') }}</th>
                    <th>
                        <a href="{{ route('reports.sales', ['sort' => 'net', 'direction' => request('sort') == 'net' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            {{ __('Net') }} @if(request('sort') == 'net'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>{{ __('User') }}</th>
                    <th>
                        <a href="{{ route('reports.sales', ['sort' => 'report_date', 'direction' => request('sort') == 'report_date' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('user_id', 'date_from', 'date_to', 'search')) }}" class="text-decoration-none">
                            {{ __('Date') }} @if(request('sort') == 'report_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                <tr>
                    <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a>
                        @if($bill->edited_at)
                            <span class="badge bg-warning text-dark ms-1" title="{{ __('Edited by') }} {{ $bill->editor?->name ?? __('Unknown') }}">{{ __('Edited') }}</span>
                        @endif
                    </td>
                    <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                    <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                    <td>{{ format_number($bill->bill_amount, 2) }}</td>
                    <td>{{ format_number($bill->discount, 2) }}</td>
                    <td class="fw-bold">{{ format_number($bill->bill_amount - $bill->discount, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ $bill->user->name ?? 'N/A' }}</span></td>
                    <td>{{ $bill->report_date?->format('M d, Y') ?? $bill->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-3">{{ __('No bills found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bills->hasPages())
    <div class="mt-3 text-center">
        {!! $bills->appends(request()->only('user_id', 'date_from', 'date_to', 'search', 'sort', 'direction'))->links() !!}
    </div>
    @endif
</div>
@endsection
