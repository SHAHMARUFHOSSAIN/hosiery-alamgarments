@extends('layouts.admin')

@section('title', __('Bills'))

@section('header', __('Bills'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Bills') }}</li>
    </ol>
</nav>
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">{{ __('Bills') }}</h2>
    <a href="{{ route('bills.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> {{ __('New Bill') }}
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-receipt text-success fs-2"></i>
                </div>
                <h3 class="mb-1">{{ format_number($totalBills) }}</h3>
                <p class="text-muted mb-0">{{ __('Total Bills') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <input type="text" name="search" class="form-control" 
                       placeholder="{{ __('Search by customer, bill no, shop...') }}" 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="bill_man" class="form-control" 
                       placeholder="{{ __('Filter by bill man...') }}" 
                       value="{{ request('bill_man') }}">
            </div>
            @if(auth()->user()->isAdmin())
            <div class="col-md-2">
                <select name="user_id" class="form-select">
                    <option value="">{{ __('All Users') }}</option>
                    @foreach($users ?? [] as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            @endif
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th><a href="{{ route('bills.index', ['sort' => 'id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">{{ __('ID') }} @if(request('sort') == 'id'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th><a href="{{ route('bills.index', ['sort' => 'bill_no', 'direction' => request('sort') == 'bill_no' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">{{ __('Bill No') }} @if(request('sort') == 'bill_no'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>{{ __('Customer') }}</th>
                    <th><a href="{{ route('bills.index', ['sort' => 'shop_name', 'direction' => request('sort') == 'shop_name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">{{ __('Shop') }} @if(request('sort') == 'shop_name'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th><a href="{{ route('bills.index', ['sort' => 'bill_man', 'direction' => request('sort') == 'bill_man' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">{{ __('Bill Man') }} @if(request('sort') == 'bill_man'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>{{ __('Payment') }}</th>
                    <th><a href="{{ route('bills.index', ['sort' => 'bill_amount', 'direction' => request('sort') == 'bill_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">{{ __('Amount') }} @if(request('sort') == 'bill_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>{{ __('Received') }}</th>
                    <th><a href="{{ route('bills.index', ['sort' => 'check_amount', 'direction' => request('sort') == 'check_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">{{ __('Cheque Amt') }} @if(request('sort') == 'check_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>{{ __('Ref Card') }}</th>
                    <th>{{ __('Discount') }}</th>
                    <th>{{ __('Due') }}</th>
                    <th>{{ __('User') }}</th>
                    <th><a href="{{ route('bills.index', ['sort' => 'report_date', 'direction' => request('sort') == 'report_date' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">{{ __('Date') }} @if(request('sort') == 'report_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                @php
                    $checkPayments = $bill->payments->where('payment_type', 'check');
                    $cardPayments = $bill->payments->where('payment_type', 'card');
                    $encashedPayments = $bill->payments->whereIn('status', ['encashed']);
                    $firstPayment = $bill->payments->first();
                    $paymentType = $firstPayment?->payment_type;
                    $receivedAmount = $encashedPayments->sum('amount');
                    $totalCheckAmount = $checkPayments->sum('check_amount');
                    $cardAmount = $cardPayments->sum('card_amount') ?: $cardPayments->sum('amount');
                    $dueAmount = $bill->bill_amount - $bill->discount - $receivedAmount - $totalCheckAmount - $cardAmount;
                @endphp
                <tr>
                    <td>{{ $bill->id }}</td>
                    <td><a href="{{ route('bills.show', ['bill' => $bill, 'page' => request('page', 1)]) }}" class="fw-semibold">{{ $bill->bill_no }}</a>
                        @if($bill->edited_at)
                            <span class="badge bg-warning text-dark ms-1" title="{{ __('Edited by') }} {{ $bill->editor?->name ?? __('Unknown') }} {{ __('on') }} {{ $bill->edited_at->format('M d, Y h:i A') }}">{{ __('Edited') }}</span>
                        @endif
                    </td>
                    <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                    <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                    <td>{{ $bill->bill_man ?? 'N/A' }}</td>
                    <td>
                        @if($paymentType === 'check')
                        <span class="badge bg-warning text-dark">{{ __('CHEQUE') }}</span>
                        @if($checkPayments->where('status', 'encashed')->count() > 0)
                        <i class="bi bi-check-circle-fill text-success"></i>
                        @endif
                        @elseif($paymentType === 'due')
                        <span class="badge bg-danger">{{ __('DUE') }}</span>
                        @elseif($paymentType === 'tt')
                        <span class="badge bg-info text-dark">{{ __('TT') }}</span>
                        @elseif($paymentType === 'cash')
                        <span class="badge bg-success">{{ __('CASH') }}</span>
                        @elseif($paymentType === 'card')
                        <span class="badge bg-secondary">{{ __('REFERENCE CARD') }}</span>
                        @else
                        <span class="badge bg-secondary">-</span>
                        @endif
                    </td>
                    <td class="fw-bold">{{ format_number($bill->bill_amount, 2) }}</td>
                    <td class="fw-bold text-success">{{ format_number($receivedAmount, 2) }}</td>
                    <td>
                        @if($totalCheckAmount > 0)
                        <span class="fw-bold text-warning">{{ format_number($totalCheckAmount, 2) }}</span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if($cardAmount > 0)
                        <span class="fw-bold text-secondary">{{ format_number($cardAmount, 2) }}</span>
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ format_number($bill->discount, 2) }}</td>
                    <td class="fw-bold {{ $dueAmount > 0 ? 'text-danger' : 'text-success' }}">{{ format_number($dueAmount > 0 ? $dueAmount : 0, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ $bill->user->name ?? 'N/A' }}</span></td>
                    <td>{{ $bill->report_date?->format('M d, Y') ?? $bill->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('bills.show', ['bill' => $bill, 'page' => request('page', 1)]) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('bills.edit', ['bill' => $bill, 'page' => request('page')]) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('bills.destroy', ['bill' => $bill, 'page' => request('page')]) }}" 
                              class="d-inline" onsubmit="return confirm('{{ __('Delete this bill?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="15" class="text-center py-3">{{ __('No bills found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bills->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $bills->links() !!}
    </div>
    @endif
</div>
@endsection