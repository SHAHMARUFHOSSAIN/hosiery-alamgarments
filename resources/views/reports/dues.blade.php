@extends('layouts.admin')

@section('title', __('Total Dues'))

@section('header', __('Total Dues'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('Reports') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Total Dues') }}</li>
    </ol>
</nav>
@endsection

@section('content')
@php
$toggleQuery = array_filter(request()->only('user_id', 'type'), fn($v) => $v !== null && $v !== '');
$showAllUrl = route('reports.dues', array_merge($toggleQuery, ['all' => 1]));
$showLastDateUrl = route('reports.dues', $toggleQuery);
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">{{ __('Branch / User') }}</label>
                <select name="user_id" class="form-select">
                    <option value="">{{ __('All Users') }}</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('Type') }}</label>
                <select name="type" class="form-select">
                    <option value="all" {{ $type == 'all' ? 'selected' : '' }}>{{ __('All') }} ({{ __('Due') }} + {{ __('Cheque') }})</option>
                    <option value="due" {{ $type == 'due' ? 'selected' : '' }}>{{ __('Due Only') }}</option>
                    <option value="cheque" {{ $type == 'cheque' ? 'selected' : '' }}>{{ __('Cheque Only') }}</option>
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
            </div>
        </form>
        @if($detailMode)
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <span class="badge bg-primary fs-6">
                <i class="bi bi-calendar-check"></i>
                @if(!$dateFrom && !$dateTo)
                {{ __('Last Bill Date') }}: @endif{{ $detailDate ? \Carbon\Carbon::parse($detailDate)->format('M d, Y') : '—' }}
            </span>
            <a href="{{ $showAllUrl }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-calendar3"></i> {{ __('Show All Dates') }}</a>
        </div>
        @else
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <span class="text-muted small">{{ __('Combined due + cheque totals by bill date') }}</span>
            <a href="{{ $showLastDateUrl }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar-day"></i>
                {{ $dateFrom && $dateTo ? __('Filter Bills by Date') : __('Show Last Date') }}
            </a>
        </div>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="bg-primary bg-opacity-10 p-3 rounded">
            <small class="text-muted">{{ __('Total Bills') }}</small>
            <h3 class="mb-0 text-primary">{{ format_number($totalBills) }}</h3>
        </div>
    </div>
    @if($type !== 'cheque')
    <div class="col-md-3">
        <div class="bg-danger bg-opacity-10 p-3 rounded">
            <small class="text-muted">{{ __('Due Amount') }}</small>
            <h3 class="mb-0 text-danger">{{ format_number($totalDue, 2) }}</h3>
        </div>
    </div>
    @endif
    @if($type !== 'due')
    <div class="col-md-3">
        <div class="bg-warning bg-opacity-10 p-3 rounded">
            <small class="text-muted">{{ __('Cheque Amount') }}</small>
            <h3 class="mb-0">{{ format_number($totalCheque, 2) }}</h3>
        </div>
    </div>
    @endif
    <div class="col-md-3">
        <div class="bg-dark bg-opacity-10 p-3 rounded">
            <small class="text-muted">{{ __('Total Amount') }}</small>
            <h3 class="mb-0 fw-bold">{{ format_number($type === 'due' ? $totalDue : ($type === 'cheque' ? $totalCheque : $totalDue + $totalCheque), 2) }}</h3>
        </div>
    </div>
</div>

@if($detailMode)
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ __('Sales of') }} {{ $detailDate ? \Carbon\Carbon::parse($detailDate)->format('M d, Y') : '—' }}</h6>
        <span class="text-muted small">{{ format_number($totalBills) }} {{ __('Bills') }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Mobile') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th class="text-end">{{ __('Bill Amount') }}</th>
                    @if($type !== 'cheque')
                    <th class="text-end">{{ __('Due Amount') }}</th>
                    @endif
                    @if($type !== 'due')
                    <th class="text-end">{{ __('Cheque Amount') }}</th>
                    @endif
                    <th class="text-end">{{ __('Total Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                @php
                $billDue = (float) $duePerBill->get($bill->id, 0);
                $billCheque = (float) $chequePerBill->get($bill->id, 0);
                $billTotal = $type === 'due' ? $billDue : ($type === 'cheque' ? $billCheque : $billDue + $billCheque);
                @endphp
                <tr>
                    <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                    <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                    <td>{{ $bill->customer->mobile ?? 'N/A' }}</td>
                    <td><span class="badge bg-secondary">{{ $bill->user->name ?? 'N/A' }}</span></td>
                    <td class="text-end">{{ format_number($bill->bill_amount, 2) }}</td>
                    @if($type !== 'cheque')
                    <td class="text-end text-danger fw-bold">{{ format_number($billDue, 2) }}</td>
                    @endif
                    @if($type !== 'due')
                    <td class="text-end fw-bold">{{ format_number($billCheque, 2) }}</td>
                    @endif
                    <td class="text-end fw-bold">{{ format_number($billTotal, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-3">{{ __('No bills found') }}</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="4">{{ __('Total') }}</td>
                    <td class="text-end">{{ format_number($totalBills ? $bills->sum(fn($b) => (float) $b->bill_amount) : 0, 2) }}</td>
                    @if($type !== 'cheque')
                    <td class="text-end text-danger">{{ format_number($totalDue, 2) }}</td>
                    @endif
                    @if($type !== 'due')
                    <td class="text-end">{{ format_number($totalCheque, 2) }}</td>
                    @endif
                    <td class="text-end">{{ format_number($type === 'due' ? $totalDue : ($type === 'cheque' ? $totalCheque : $totalDue + $totalCheque), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@else
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th class="text-center">{{ __('Bills') }}</th>
                    @if($type !== 'cheque')
                    <th class="text-center">{{ __('Due Bills') }}</th>
                    <th class="text-end">{{ __('Due Amount') }}</th>
                    @endif
                    @if($type !== 'due')
                    <th class="text-center">{{ __('Cheque Bills') }}</th>
                    <th class="text-end">{{ __('Cheque Amount') }}</th>
                    @endif
                    <th class="text-end">{{ __('Total Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->bill_date)->format('M d, Y') }}</td>
                    <td class="text-center">{{ format_number($row->bill_count) }}</td>
                    @if($type !== 'cheque')
                    <td class="text-center">{{ format_number($row->due_bills) }}</td>
                    <td class="text-end text-danger fw-bold">{{ format_number($row->due_total, 2) }}</td>
                    @endif
                    @if($type !== 'due')
                    <td class="text-center">{{ format_number($row->cheque_bills) }}</td>
                    <td class="text-end fw-bold">{{ format_number($row->cheque_total, 2) }}</td>
                    @endif
                    <td class="text-end fw-bold">{{ format_number($type === 'due' ? $row->due_total : ($type === 'cheque' ? $row->cheque_total : $row->due_total + $row->cheque_total), 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3">{{ __('No dues found') }}</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td>{{ __('Total') }}</td>
                    <td class="text-center">{{ format_number($totalBills) }}</td>
                    @if($type !== 'cheque')
                    <td class="text-center">{{ format_number($totalDueBills) }}</td>
                    <td class="text-end text-danger">{{ format_number($totalDue, 2) }}</td>
                    @endif
                    @if($type !== 'due')
                    <td class="text-center">{{ format_number($totalChequeBills) }}</td>
                    <td class="text-end">{{ format_number($totalCheque, 2) }}</td>
                    @endif
                    <td class="text-end">{{ format_number($type === 'due' ? $totalDue : ($type === 'cheque' ? $totalCheque : $totalDue + $totalCheque), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @if($rows->hasPages())
    <div class="mt-3 text-center">
        {!! $rows->render() !!}
    </div>
    @endif
</div>
@endif
@endsection