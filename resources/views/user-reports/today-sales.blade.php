@extends('layouts.admin')

@section('title', __('Sales Report') . ' - ' . $reportDate)
@section('header', __('Sales Report'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user-reports.index') }}">{{ __('My Reports') }}</a></li>
        <li class="breadcrumb-item active">{{ $reportDate }}</li>
    </ol>
</nav>
@endsection

@section('content')
@php
    $isClosed = $existingReport && $existingReport->status === 'closed';
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Select Date') }}</label>
                <input type="date" name="date" class="form-control" value="{{ $reportDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block"><i class="bi bi-search"></i> {{ __('View Report') }}</button>
            </div>
        </form>
    </div>
</div>

@if($isClosed)
<div class="alert alert-info d-flex justify-content-between align-items-center">
    <span><i class="bi bi-lock-fill"></i> {{ __('This report was closed on') }} {{ $existingReport->closed_at?->format('M d, Y h:i A') }} {{ __('by') }} {{ $existingReport->closer?->name ?? 'N/A' }}.</span>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Bills') }}</h6>
                <h3 class="text-primary mb-0">{{ $totalBills }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Gross Sales') }}</h6>
                <h3 class="text-primary mb-0">{{ format_currency($grossAmount) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Received') }}</h6>
                <h3 class="text-success mb-0">{{ format_currency($totalReceived) }}</h3>
            </div>
        </div>
    </div>
    </div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">{{ __('Cheque Amount') }}</h6>
                <h5 class="mb-0">{{ format_currency($chequeAmt) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">{{ __('Ref Card Amount') }}</h6>
                <h5 class="mb-0">{{ format_currency($refCardAmt) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">{{ __('Cash Amount') }}</h6>
                <h5 class="mb-0">{{ format_currency($cashAmt) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">{{ __('TT Amount') }}</h6>
                <h5 class="mb-0">{{ format_currency($ttAmt) }}</h5>
            </div>
        </div>
    </div>
</div>

@if($isClosed)
@php
    $finalCalc = max(0, $grossAmount - $billDiscount - $existingReport->discount_amt);
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="text-muted mb-1">{{ __('Bill Discount') }}</h6>
                <h4 class="text-danger mb-0">- {{ format_currency($billDiscount) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="text-muted mb-1">{{ __('Rep Discount') }}</h6>
                <h4 class="text-danger mb-0">- {{ format_currency($existingReport->discount_amt) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="text-muted mb-1">{{ __('Due Amount') }}</h6>
                <h4 class="text-warning mb-0">{{ format_currency($existingReport->due_amt) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border border-success">
            <div class="card-body">
                <h6 class="text-muted mb-1">{{ __('Final Amount') }}</h6>
                <h4 class="text-success mb-0">{{ format_currency($finalCalc) }}</h4>
            </div>
        </div>
    </div>
</div>
@endif

@if(!$isClosed)
<div class="text-end mb-4">
    <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#closeReportModal">
        <i class="bi bi-check-circle"></i> {{ __('Close Report') }}
    </button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Bills') }} ({{ $totalBills }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Shop') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Discount') }}</th>
                    <th class="text-end">{{ __('Net') }}</th>
                    <th>{{ __('Payment') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayBills as $bill)
                <tr>
                    <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                    <td>{{ $bill->customer?->name ?? 'N/A' }}</td>
                    <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                    <td class="text-end">{{ format_currency($bill->bill_amount) }}</td>
                    <td class="text-end">{{ format_currency($bill->discount) }}</td>
                    <td class="text-end fw-bold">{{ format_currency($bill->bill_amount - $bill->discount) }}</td>
                    <td>
                        @php
                            $bp = $bill->payments->first();
                        @endphp
                        @if($bp)
                            <span class="badge bg-{{ $bp->payment_type === 'cash' ? 'success' : ($bp->payment_type === 'check' ? 'warning text-dark' : ($bp->payment_type === 'card' ? 'info text-dark' : 'secondary')) }}">
                                {{ ucfirst($bp->payment_type) }}
                            </span>
                        @else
                            <span class="badge bg-secondary">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4">{{ __('No bills found for') }} {{ $reportDate }}</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-primary">
                <tr>
                    <td colspan="3"><strong>{{ __('Total') }}</strong></td>
                    <td class="text-end"><strong>{{ format_currency($grossAmount) }}</strong></td>
                    <td class="text-end"><strong>{{ format_currency($todayBills->sum('discount')) }}</strong></td>
                    <td class="text-end"><strong>{{ format_currency($grossAmount - $todayBills->sum('discount')) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@if(!$isClosed)
<div class="modal fade" id="closeReportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('user-reports.today-sales.close') }}">
                @csrf
                <input type="hidden" name="report_date" value="{{ $reportDate }}">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Close Sales Report') }} - {{ $reportDate }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Gross Sales Amount') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="text" class="form-control" value="{{ format_number($grossAmount, 2) }}" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Total Discount Amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="discount_amt" id="discountAmt" class="form-control"
                                   value="0" min="0" max="{{ $grossAmount }}" required>
                        </div>
                        <div class="form-text">{{ __('Deduct this from Gross Sales to get Final Sales Amount.') }}</div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <strong>{{ __('Final Sales Amount:') }}</strong>
                        <span id="finalAmountDisplay">{{ format_currency($grossAmount) }}</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> {{ __('Confirm Close') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if(!$isClosed)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const discountInput = document.getElementById('discountAmt');
    const finalDisplay = document.getElementById('finalAmountDisplay');
    const grossAmount = {{ $grossAmount }};

    if (discountInput && finalDisplay) {
        function updateFinal() {
            const discount = parseFloat(discountInput.value) || 0;
            const finalAmt = Math.max(0, grossAmount - discount);
            finalDisplay.textContent = '\u09f3' + finalAmt.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        discountInput.addEventListener('input', updateFinal);
        discountInput.addEventListener('change', updateFinal);
    }
});
</script>
@endif
@endpush
