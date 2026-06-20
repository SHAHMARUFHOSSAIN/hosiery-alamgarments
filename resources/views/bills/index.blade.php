@extends('layouts.admin')

@section('title', 'Bills')

@section('header', 'Bills')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Bills</li>
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
    <h2 class="mb-0">Bills</h2>
    <a href="{{ route('bills.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> New Bill
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-receipt text-success fs-2"></i>
                </div>
                <h3 class="mb-1">{{ number_format($totalBills) }}</h3>
                <p class="text-muted mb-0">Total Bills</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search bill no, shop, bill man..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="bill_man" class="form-control" 
                       placeholder="Filter by bill man..." 
                       value="{{ request('bill_man') }}">
            </div>
            @if(auth()->user()->isAdmin())
            <div class="col-md-2">
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
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
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th><a href="{{ route('bills.index', ['sort' => 'id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">ID @if(request('sort') == 'id'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th><a href="{{ route('bills.index', ['sort' => 'bill_no', 'direction' => request('sort') == 'bill_no' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">Bill No @if(request('sort') == 'bill_no'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>Customer</th>
                    <th><a href="{{ route('bills.index', ['sort' => 'shop_name', 'direction' => request('sort') == 'shop_name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">Shop @if(request('sort') == 'shop_name'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th><a href="{{ route('bills.index', ['sort' => 'bill_man', 'direction' => request('sort') == 'bill_man' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">Bill Man @if(request('sort') == 'bill_man'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>Payment</th>
                    <th><a href="{{ route('bills.index', ['sort' => 'bill_amount', 'direction' => request('sort') == 'bill_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">Amount @if(request('sort') == 'bill_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>Received</th>
                    <th><a href="{{ route('bills.index', ['sort' => 'check_amount', 'direction' => request('sort') == 'check_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">Cheque Amt @if(request('sort') == 'check_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>Ref Card</th>
                    <th>Discount</th>
                    <th>Due</th>
                    <th>User</th>
                    <th><a href="{{ route('bills.index', ['sort' => 'report_date', 'direction' => request('sort') == 'report_date' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">Date @if(request('sort') == 'report_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif</a></th>
                    <th>Actions</th>
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
                    <td><a href="{{ route('bills.show', $bill) }}" class="fw-semibold">{{ $bill->bill_no }}</a>
                        @if($bill->edited_at)
                            <span class="badge bg-warning text-dark ms-1" title="Edited by {{ $bill->editor?->name ?? 'Unknown' }} on {{ $bill->edited_at->format('M d, Y h:i A') }}">Edited</span>
                        @endif
                    </td>
                    <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                    <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                    <td>{{ $bill->bill_man ?? 'N/A' }}</td>
                    <td>
                        @if($paymentType === 'check')
                        <span class="badge bg-warning text-dark">CHEQUE</span>
                        @if($checkPayments->where('status', 'encashed')->count() > 0)
                        <i class="bi bi-check-circle-fill text-success"></i>
                        @endif
                        @elseif($paymentType === 'due')
                        <span class="badge bg-danger">DUE</span>
                        @elseif($paymentType === 'tt')
                        <span class="badge bg-info text-dark">TT</span>
                        @elseif($paymentType === 'cash')
                        <span class="badge bg-success">CASH</span>
                        @elseif($paymentType === 'card')
                        <span class="badge bg-secondary">REFERENCE CARD</span>
                        @else
                        <span class="badge bg-secondary">-</span>
                        @endif
                    </td>
                    <td class="fw-bold">{{ number_format($bill->bill_amount, 2) }}</td>
                    <td class="fw-bold text-success">{{ number_format($receivedAmount, 2) }}</td>
                    <td>
                        @if($totalCheckAmount > 0)
                        <span class="fw-bold text-warning">{{ number_format($totalCheckAmount, 2) }}</span>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if($cardAmount > 0)
                        <span class="fw-bold text-secondary">{{ number_format($cardAmount, 2) }}</span>
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ number_format($bill->discount, 2) }}</td>
                    <td class="fw-bold {{ $dueAmount > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($dueAmount > 0 ? $dueAmount : 0, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ $bill->user->name ?? 'N/A' }}</span></td>
                    <td>{{ $bill->report_date?->format('M d, Y') ?? $bill->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($bill->isEditable())
                        <a href="{{ route('bills.edit', $bill) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                        @if($bill->isDeletable())
                        <form method="POST" action="{{ route('bills.destroy', $bill) }}" 
                              class="d-inline" onsubmit="return confirm('Delete this bill?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="15" class="text-center py-3">No bills found</td></tr>
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