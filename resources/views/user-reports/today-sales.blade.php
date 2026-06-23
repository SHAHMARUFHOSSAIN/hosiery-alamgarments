@extends('layouts.admin')

@section('title', 'Sales Report - ' . $reportDate)
@section('header', 'Sales Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('user-reports.index') }}">My Reports</a></li>
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
                <label class="form-label">Select Date</label>
                <input type="date" name="date" class="form-control" value="{{ $reportDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block"><i class="bi bi-search"></i> View Report</button>
            </div>
        </form>
    </div>
</div>

@if($isClosed)
<div class="alert alert-info d-flex justify-content-between align-items-center">
    <span><i class="bi bi-lock-fill"></i> This report was closed on {{ $existingReport->closed_at?->format('M d, Y h:i A') }} by {{ $existingReport->closer?->name ?? 'N/A' }}.</span>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Bills</h6>
                <h3 class="text-primary mb-0">{{ $totalBills }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Gross Sales</h6>
                <h3 class="text-primary mb-0">৳{{ number_format($grossAmount, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Received</h6>
                <h3 class="text-success mb-0">৳{{ number_format($totalReceived, 2) }}</h3>
            </div>
        </div>
    </div>
    </div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Cheque Amount</h6>
                <h5 class="mb-0">৳{{ number_format($chequeAmt, 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Ref Card Amount</h6>
                <h5 class="mb-0">৳{{ number_format($refCardAmt, 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Cash Amount</h6>
                <h5 class="mb-0">৳{{ number_format($cashAmt, 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">TT Amount</h6>
                <h5 class="mb-0">৳{{ number_format($ttAmt, 2) }}</h5>
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
                <h6 class="text-muted mb-1">Bill Discount</h6>
                <h4 class="text-danger mb-0">- ৳{{ number_format($billDiscount, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="text-muted mb-1">Rep Discount</h6>
                <h4 class="text-danger mb-0">- ৳{{ number_format($existingReport->discount_amt, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="text-muted mb-1">Due Amount</h6>
                <h4 class="text-warning mb-0">৳{{ number_format($existingReport->due_amt, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border border-success">
            <div class="card-body">
                <h6 class="text-muted mb-1">Final Amount</h6>
                <h4 class="text-success mb-0">৳{{ number_format($finalCalc, 2) }}</h4>
            </div>
        </div>
    </div>
</div>
@endif

@if(!$isClosed)
<div class="text-end mb-4">
    <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#closeReportModal">
        <i class="bi bi-check-circle"></i> Close Report
    </button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Bills ({{ $totalBills }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bill No</th>
                    <th>Customer</th>
                    <th>Shop</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Net</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayBills as $bill)
                <tr>
                    <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                    <td>{{ $bill->customer?->name ?? 'N/A' }}</td>
                    <td>{{ $bill->shop_name ?? 'N/A' }}</td>
                    <td class="text-end">৳{{ number_format($bill->bill_amount, 2) }}</td>
                    <td class="text-end">৳{{ number_format($bill->discount, 2) }}</td>
                    <td class="text-end fw-bold">৳{{ number_format($bill->bill_amount - $bill->discount, 2) }}</td>
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
                <tr><td colspan="7" class="text-center py-4">No bills found for {{ $reportDate }}</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-primary">
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td class="text-end"><strong>৳{{ number_format($grossAmount, 2) }}</strong></td>
                    <td class="text-end"><strong>৳{{ number_format($todayBills->sum('discount'), 2) }}</strong></td>
                    <td class="text-end"><strong>৳{{ number_format($grossAmount - $todayBills->sum('discount'), 2) }}</strong></td>
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
                    <h5 class="modal-title">Close Sales Report - {{ $reportDate }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Gross Sales Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="text" class="form-control" value="{{ number_format($grossAmount, 2) }}" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Discount Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="discount_amt" id="discountAmt" class="form-control"
                                   value="0" min="0" max="{{ $grossAmount }}" required>
                        </div>
                        <div class="form-text">Deduct this from Gross Sales to get Final Sales Amount.</div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <strong>Final Sales Amount:</strong>
                        <span id="finalAmountDisplay">৳{{ number_format($grossAmount, 2) }}</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Confirm Close
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
