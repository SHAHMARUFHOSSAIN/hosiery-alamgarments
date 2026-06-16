@extends('layouts.admin')

@section('title', 'Reference Card Payments')
@section('header', 'Reference Card Payments')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Bill no or customer..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('card-payments.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h5 class="mb-0">Pending Reference Card Payments ({{ $cardPayments->total() }})</h5>
    <h5 class="mb-0">Total Pending: <span class="text-danger fw-bold">৳{{ number_format($totalPending, 2) }}</span></h5>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bill No</th>
                    <th>Customer</th>
                    <th>
                        <a href="{{ route('card-payments.index', ['sort' => 'card_reference', 'direction' => request('sort') == 'card_reference' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('search', 'date_from', 'date_to')) }}" class="text-decoration-none">
                            Reference Card @if(request('sort') == 'card_reference'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('card-payments.index', ['sort' => 'card_location', 'direction' => request('sort') == 'card_location' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('search', 'date_from', 'date_to')) }}" class="text-decoration-none">
                            Location @if(request('sort') == 'card_location'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('card-payments.index', ['sort' => 'card_amount', 'direction' => request('sort') == 'card_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('search', 'date_from', 'date_to')) }}" class="text-decoration-none">
                            Amount @if(request('sort') == 'card_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('card-payments.index', ['sort' => 'card_date', 'direction' => request('sort') == 'card_date' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('search', 'date_from', 'date_to')) }}" class="text-decoration-none">
                            Card Date @if(request('sort') == 'card_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('card-payments.index', ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('search', 'date_from', 'date_to')) }}" class="text-decoration-none">
                            Created @if(request('sort') == 'created_at'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cardPayments as $payment)
                <tr>
                    <td>
                        <a href="{{ route('bills.show', $payment->bill) }}">{{ $payment->bill->bill_no ?? 'N/A' }}</a>
                    </td>
                    <td>
                        <strong>{{ $payment->bill->customer->name ?? 'Unknown' }}</strong>
                        @if($payment->bill->customer->mobile)
                        <br><small>{{ $payment->bill->customer->mobile }}</small>
                        @endif
                    </td>
                    <td>{{ $payment->card_reference ?? 'N/A' }}</td>
                    <td>{{ $payment->card_location ?? 'N/A' }}</td>
                    <td class="fw-bold">৳{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->card_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                    <td>
                        <button type="button" class="btn btn-success btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#encashModal{{ $payment->id }}">
                            <i class="bi bi-cash-coin"></i> Encash
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4"><strong>No pending reference card payments found</strong></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cardPayments->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $cardPayments->appends(request()->only('search', 'date_from', 'date_to', 'sort', 'direction'))->links() !!}
    </div>
    @endif
</div>

@foreach($cardPayments as $payment)
<div class="modal fade" id="encashModal{{ $payment->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Encash Reference Card Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('card-payments.encash', $payment) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Customer:</strong> {{ $payment->bill->customer->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Bill No:</strong> {{ $payment->bill->bill_no ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Reference Card:</strong> {{ $payment->card_reference ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Location:</strong> {{ $payment->card_location ?? 'N/A' }}
                    </div>
                    <div class="mb-3 alert alert-warning">
                        <strong>Amount to collect:</strong>
                        <span class="text-danger fw-bold fs-5">৳{{ number_format($payment->amount, 2) }}</span>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle"></i>
                        This will mark the payment as collected and add
                        <strong>৳{{ number_format($payment->amount, 2) }}</strong> to Main Balance.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-cash-coin"></i> Confirm Encash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
