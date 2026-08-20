@extends('layouts.admin')

@section('title', __('Dashboard'))
@section('header', __('Dashboard'))

@section('content')
@if($stats['todayDues'] > 0)
<div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle fs-4"></i>
    <div>
        <strong>{{ $stats['todayDues'] }} {{ __('dues') }}</strong> {{ __('due today worth') }} {{ format_number($stats['totalDues'], 2) }}
        <a href="{{ route('dues.daily-report') }}" class="alert-link">{{ __('View Report') }}</a>
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">{{ __('Today\'s Sales') }}</p>
                        <h3 class="mb-0 fw-bold text-primary">{{ format_currency($stats['todaySales']) }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-2 rounded">
                        <i class="bi bi-cash-stack text-primary"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    {{ __('Month:') }} <strong>{{ format_currency($stats['thisMonthSales']) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">{{ __('Pending Dues') }}</p>
                        <h3 class="mb-0 fw-bold text-danger">{{ format_currency($stats['totalDues']) }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-2 rounded">
                        <i class="bi bi-clock-history text-danger"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    {{ __('My Bills:') }} <strong>{{ format_number($stats['totalBills']) }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">{{ __('My Customers') }}</p>
                        <h3 class="mb-0 fw-bold text-info">{{ format_number($stats['totalCustomers']) }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-2 rounded">
                        <i class="bi bi-people text-info"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    {{ __('Discount:') }} <strong class="text-danger">{{ format_currency($stats['thisMonthDiscount']) }}</strong>
                    @if(($thisMonthRepDiscount ?? 0) > 0)
                    | {{ __('Rep Discount:') }} <strong class="text-danger">{{ format_currency($thisMonthRepDiscount) }}</strong>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">{{ __('Sales Trend (Last 7 Days)') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">{{ __('Payment Breakdown (This Month)') }}</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-cash text-success me-2"></i>{{ __('Cash') }}</span>
                        <span class="fw-bold">{{ format_currency($stats['cashSales']) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-bank text-warning me-2"></i>{{ __('Cheque') }}</span>
                        <span class="fw-bold">{{ format_currency($stats['checkSales']) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-telephone text-info me-2"></i>{{ __('TT') }}</span>
                        <span class="fw-bold">{{ format_currency($stats['ttSales']) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-credit-card text-secondary me-2"></i>{{ __('Reference Card') }}</span>
                        <span class="fw-bold">{{ format_currency($stats['cardSales']) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-hourglass-split text-danger me-2"></i>{{ __('Due') }}</span>
                        <span class="fw-bold">{{ format_currency($stats['dueSales']) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2"></i>{{ __('Daily Sales Report') }}</h6>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('dashboard', ['sales_filter' => 'today']) }}" class="btn btn-outline-{{ $filter === 'today' ? 'primary' : 'secondary' }}">{{ __('Today') }}</a>
                        <a href="{{ route('dashboard', ['sales_filter' => 'yesterday']) }}" class="btn btn-outline-{{ $filter === 'yesterday' ? 'primary' : 'secondary' }}">{{ __('Yesterday') }}</a>
                        <a href="{{ route('dashboard', ['sales_filter' => '7days']) }}" class="btn btn-outline-{{ $filter === '7days' ? 'primary' : 'secondary' }}">{{ __('Last 7 Days') }}</a>
                    </div>
                    <span class="badge bg-primary">{{ $label }}: {{ format_currency($totalFiltered) }}</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Bills') }}</th>
                            <th>{{ __('Customers') }}</th>
                            <th class="text-end">{{ __('Subtotal') }}</th>
                            <th class="text-end">{{ __('Discount') }}</th>
                            <th class="text-end">{{ __('Rep Disc') }}</th>
                            <th class="text-end">{{ __('Net') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($weekBills->sortByDesc(function ($day, $date) { return $date; }) as $date => $bills)
                        <tr class="{{ $date === now()->format('Y-m-d') && $filter !== 'yesterday' ? 'table-primary' : '' }}">
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($date)->format('l, M d') }}</strong>
                                @if($date === now()->format('Y-m-d'))
                                <span class="badge bg-success ms-1">{{ __('Today') }}</span>
                                @endif
                            </td>
                            <td>{{ $bills->count() }}</td>
                            <td>
                                @php
                                    $customers = $bills->pluck('customer.name')->filter()->unique()->toArray();
                                @endphp
                                {{ implode(', ', array_slice($customers, 0, 3)) }}
                                @if(count($customers) > 3)<span class="text-muted">+{{ count($customers) - 3 }}</span>@endif
                            </td>
                            <td class="text-end fw-bold">{{ format_currency($bills->sum('bill_amount')) }}</td>
                            <td class="text-end text-danger">{{ format_currency($bills->sum('discount')) }}</td>
                            <td class="text-end text-danger">{{ format_currency($dailyRepDiscounts[$date]->total ?? 0) }}</td>
                            <td class="text-end fw-bold text-success">{{ format_currency($bills->sum('bill_amount') - $bills->sum('discount') - ($dailyRepDiscounts[$date]->total ?? 0)) }}</td>
                        </tr>
                        @foreach($bills->take(5) as $bill)
                        <tr class="table-light" style="font-size: 0.9em;">
                            <td></td>
                            <td colspan="1"><small class="text-muted">{{ $bill->bill_no }}</small>
                                @if($bill->edited_at)
                                    <small class="badge bg-warning text-dark ms-1">{{ __('Edited') }}</small>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $bill->customer->name ?? 'N/A' }}</small></td>
                            <td class="text-end"><small>{{ format_currency($bill->bill_amount) }}</small></td>
                            <td class="text-end"><small class="text-danger">{{ format_currency($bill->discount) }}</small></td>
                            <td class="text-end"><small class="text-muted">—</small></td>
                            <td class="text-end"><small class="text-success">{{ format_currency($bill->bill_amount - $bill->discount) }}</small></td>
                        </tr>
                        @endforeach
                        @if($bills->count() > 5)
                        <tr>
                            <td colspan="7" class="text-center text-muted"><small>+{{ $bills->count() - 5 }} {{ __('more bill(s)') }}</small></td>
                        </tr>
                        @endif
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">{{ __('No sales for this period') }}</td></tr>
                        @endforelse
                        @if($weekBills->isNotEmpty())
                        @php
                            $totalRepDisc = $dailyRepDiscounts->sum('total');
                        @endphp
                        <tr class="table-dark">
                            <td colspan="3" class="text-end fw-bold">{{ __('TOTAL') }}</td>
                            <td class="text-end fw-bold">{{ format_currency($weekBills->flatten()->sum('bill_amount')) }}</td>
                            <td class="text-end fw-bold text-danger">{{ format_currency($weekBills->flatten()->sum('discount')) }}</td>
                            <td class="text-end fw-bold text-danger">{{ format_currency($totalRepDisc) }}</td>
                            <td class="text-end fw-bold text-success">{{ format_currency($weekBills->flatten()->sum('bill_amount') - $weekBills->flatten()->sum('discount') - $totalRepDisc) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">{{ __('Recent Bills') }}</h6>
                <a href="{{ route('bills.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Bill No') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBills as $bill)
                        <tr>
                            <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a>
                                @if($bill->edited_at)
                                    <span class="badge bg-warning text-dark ms-1" title="{{ __('Edited by') }} {{ $bill->editor?->name ?? __('Unknown') }}">{{ __('Edited') }}</span>
                                @endif
                            </td>
                            <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                            <td class="text-end fw-bold">{{ format_currency($bill->bill_amount) }}</td>
                            <td>{{ $bill->report_date?->format('M d') ?? $bill->created_at->format('M d') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3">{{ __('No bills found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">{{ __('My Pending Dues') }}</h6>
        <a href="{{ route('dues.daily-report') }}" class="btn btn-sm btn-warning">{{ __('Today') }}</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Original') }}</th>
                    <th>{{ __('Paid') }}</th>
                    <th class="text-end">{{ __('Remaining') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDues as $due)
                <tr class="{{ $due->due_date->isPast() ? 'table-danger' : '' }}">
                    <td>{{ $due->customer->name ?? 'N/A' }}</td>
                    <td>{{ $due->bill->bill_no ?? 'N/A' }}</td>
                    <td>{{ format_currency($due->original_amount) }}</td>
                    <td class="text-success">{{ format_currency($due->total_paid) }}</td>
                    <td class="text-end text-danger fw-bold">{{ format_currency($due->remaining_amount) }}</td>
                    <td><span class="badge bg-warning text-dark">{{ $due->due_date->format('M d') }}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#userDashPayModal{{ $due->id }}">
                            <i class="bi bi-credit-card"></i> {{ __('Pay') }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3 text-success"><i class="bi bi-check-circle-fill me-1"></i>{{ __('No pending dues') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-warning"></i>{{ __('My Pending Cheques') }}</h6>
        <a href="{{ route('dues.checks-report') }}" class="btn btn-sm btn-outline-warning">{{ __('View All') }}</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Bank') }}</th>
                    <th>{{ __('Cheque No') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th>{{ __('Cheque Date') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingCheques as $cheque)
                @php $remaining = $cheque->check_amount - $cheque->encashed_amount; @endphp
                <tr>
                    <td><a href="{{ route('bills.show', $cheque->bill) }}">{{ $cheque->bill->bill_no ?? 'N/A' }}</a></td>
                    <td>{{ $cheque->bill->customer->name ?? 'N/A' }}</td>
                    <td>{{ $cheque->bank_name ?? 'N/A' }}</td>
                    <td>{{ $cheque->check_no ?? 'N/A' }}</td>
                    <td class="text-end fw-bold">{{ format_currency($remaining) }}</td>
                    <td>{{ $cheque->check_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('bills.show', $cheque->bill) }}" class="btn btn-sm btn-success py-0 px-2">
                            <i class="bi bi-credit-card"></i> {{ __('Encash') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3 text-muted">{{ __('No pending cheques') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($recentDues as $due)
<div class="modal fade" id="userDashPayModal{{ $due->id }}" tabindex="-1">
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
@endforeach
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim() || '#0d6efd';

    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: @json($dailyLabels),
            datasets: [{
                label: 'Sales',
                data: @json($dailyValues),
                borderColor: primaryColor,
                backgroundColor: primaryColor + '20',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '৳' + v.toLocaleString() } }
            }
        }
    });
});
</script>
@endsection
