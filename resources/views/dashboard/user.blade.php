@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
@if($stats['todayDues'] > 0)
<div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle fs-4"></i>
    <div>
        <strong>{{ $stats['todayDues'] }} dues</strong> due today worth {{ number_format($stats['totalDues'], 2) }}
        <a href="{{ route('dues.daily-report') }}" class="alert-link">View Report</a>
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">Today's Sales</p>
                        <h3 class="mb-0 fw-bold text-primary">৳{{ number_format($stats['todaySales'], 2) }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-2 rounded">
                        <i class="bi bi-cash-stack text-primary"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    Month: <strong>৳{{ number_format($stats['thisMonthSales'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">My Balance</p>
                        <h3 class="mb-0 fw-bold text-{{ $stats['mainBalance'] >= 0 ? 'success' : 'danger' }}">৳{{ number_format($stats['mainBalance'], 2) }}</h3>
                    </div>
                    <div class="bg-{{ $stats['mainBalance'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 p-2 rounded">
                        <i class="bi bi-wallet2 text-{{ $stats['mainBalance'] >= 0 ? 'success' : 'danger' }}"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    Total Sales: <strong>৳{{ number_format($stats['totalSales'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">Pending Dues</p>
                        <h3 class="mb-0 fw-bold text-danger">৳{{ number_format($stats['totalDues'], 2) }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-2 rounded">
                        <i class="bi bi-clock-history text-danger"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    My Bills: <strong>{{ number_format($stats['totalBills']) }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">My Customers</p>
                        <h3 class="mb-0 fw-bold text-info">{{ number_format($stats['totalCustomers']) }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-2 rounded">
                        <i class="bi bi-people text-info"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    Discount (Month): <strong class="text-danger">৳{{ number_format($stats['thisMonthDiscount'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Sales Trend (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Payment Breakdown (This Month)</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-cash text-success me-2"></i>Cash</span>
                        <span class="fw-bold">৳{{ number_format($stats['cashSales'], 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-bank text-warning me-2"></i>Cheque</span>
                        <span class="fw-bold">৳{{ number_format($stats['checkSales'], 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-telephone text-info me-2"></i>TT</span>
                        <span class="fw-bold">৳{{ number_format($stats['ttSales'], 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-credit-card text-secondary me-2"></i>Reference Card</span>
                        <span class="fw-bold">৳{{ number_format($stats['cardSales'], 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-hourglass-split text-danger me-2"></i>Due</span>
                        <span class="fw-bold">৳{{ number_format($stats['dueSales'], 2) }}</span>
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
                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2"></i>Daily Sales Report</h6>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('dashboard', ['sales_filter' => 'today']) }}" class="btn btn-outline-{{ $filter === 'today' ? 'primary' : 'secondary' }}">Today</a>
                        <a href="{{ route('dashboard', ['sales_filter' => 'yesterday']) }}" class="btn btn-outline-{{ $filter === 'yesterday' ? 'primary' : 'secondary' }}">Yesterday</a>
                        <a href="{{ route('dashboard', ['sales_filter' => '7days']) }}" class="btn btn-outline-{{ $filter === '7days' ? 'primary' : 'secondary' }}">Last 7 Days</a>
                    </div>
                    <span class="badge bg-primary">{{ $label }}: ৳{{ number_format($totalFiltered, 2) }}</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Bills</th>
                            <th>Customers</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($weekBills->sortByDesc(function ($day, $date) { return $date; }) as $date => $bills)
                        <tr class="{{ $date === now()->format('Y-m-d') && $filter !== 'yesterday' ? 'table-primary' : '' }}">
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($date)->format('l, M d') }}</strong>
                                @if($date === now()->format('Y-m-d'))
                                <span class="badge bg-success ms-1">Today</span>
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
                            <td class="text-end fw-bold">৳{{ number_format($bills->sum('bill_amount'), 2) }}</td>
                            <td class="text-end text-danger">৳{{ number_format($bills->sum('discount'), 2) }}</td>
                            <td class="text-end fw-bold text-success">৳{{ number_format($bills->sum('bill_amount') - $bills->sum('discount'), 2) }}</td>
                        </tr>
                        @foreach($bills->take(5) as $bill)
                        <tr class="table-light" style="font-size: 0.9em;">
                            <td></td>
                            <td colspan="1"><small class="text-muted">{{ $bill->bill_no }}</small></td>
                            <td><small class="text-muted">{{ $bill->customer->name ?? 'N/A' }}</small></td>
                            <td class="text-end"><small>৳{{ number_format($bill->bill_amount, 2) }}</small></td>
                            <td class="text-end"><small class="text-danger">৳{{ number_format($bill->discount, 2) }}</small></td>
                            <td class="text-end"><small class="text-success">৳{{ number_format($bill->bill_amount - $bill->discount, 2) }}</small></td>
                        </tr>
                        @endforeach
                        @if($bills->count() > 5)
                        <tr>
                            <td colspan="6" class="text-center text-muted"><small>+{{ $bills->count() - 5 }} more bill(s)</small></td>
                        </tr>
                        @endif
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No sales for this period</td></tr>
                        @endforelse
                        @if($weekBills->isNotEmpty())
                        <tr class="table-dark">
                            <td colspan="3" class="text-end fw-bold">TOTAL</td>
                            <td class="text-end fw-bold">৳{{ number_format($weekBills->flatten()->sum('bill_amount'), 2) }}</td>
                            <td class="text-end fw-bold text-danger">৳{{ number_format($weekBills->flatten()->sum('discount'), 2) }}</td>
                            <td class="text-end fw-bold text-success">৳{{ number_format($weekBills->flatten()->sum('bill_amount') - $weekBills->flatten()->sum('discount'), 2) }}</td>
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
                <h6 class="mb-0 fw-bold">Recent Bills</h6>
                <a href="{{ route('bills.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bill No</th>
                            <th>Customer</th>
                            <th class="text-end">Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBills as $bill)
                        <tr>
                            <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                            <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                            <td class="text-end fw-bold">৳{{ number_format($bill->bill_amount, 2) }}</td>
                            <td>{{ $bill->created_at->format('M d') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3">No bills found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Recent Transactions</h6>
                <a href="{{ route('main-balance.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $tx)
                        <tr>
                            <td>{{ Str::limit($tx->name, 35) }}</td>
                            <td class="text-end fw-bold text-{{ $tx->type === 'credit' ? 'success' : 'danger' }}">
                                {{ $tx->type === 'credit' ? '+' : '-' }}৳{{ number_format($tx->amount, 2) }}
                            </td>
                            <td><small>{{ $tx->created_at->format('M d H:i') }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-3">No transactions</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">My Pending Dues</h6>
        <a href="{{ route('dues.daily-report') }}" class="btn btn-sm btn-warning">Today</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Customer</th>
                    <th>Bill No</th>
                    <th>Original</th>
                    <th>Paid</th>
                    <th class="text-end">Remaining</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDues as $due)
                <tr class="{{ $due->due_date->isPast() ? 'table-danger' : '' }}">
                    <td>{{ $due->customer->name ?? 'N/A' }}</td>
                    <td>{{ $due->bill->bill_no ?? 'N/A' }}</td>
                    <td>৳{{ number_format($due->original_amount, 2) }}</td>
                    <td class="text-success">৳{{ number_format($due->total_paid, 2) }}</td>
                    <td class="text-end text-danger fw-bold">৳{{ number_format($due->remaining_amount, 2) }}</td>
                    <td><span class="badge bg-warning text-dark">{{ $due->due_date->format('M d') }}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#userDashPayModal{{ $due->id }}">
                            <i class="bi bi-credit-card"></i> Pay
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3 text-success"><i class="bi bi-check-circle-fill me-1"></i>No pending dues</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-warning"></i>My Pending Cheques</h6>
        <a href="{{ route('dues.checks-report') }}" class="btn btn-sm btn-outline-warning">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bill No</th>
                    <th>Customer</th>
                    <th>Bank</th>
                    <th>Cheque No</th>
                    <th class="text-end">Amount</th>
                    <th>Cheque Date</th>
                    <th>Action</th>
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
                    <td class="text-end fw-bold">৳{{ number_format($remaining, 2) }}</td>
                    <td>{{ $cheque->check_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('bills.show', $cheque->bill) }}" class="btn btn-sm btn-success py-0 px-2">
                            <i class="bi bi-credit-card"></i> Encash
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3 text-muted">No pending cheques</td></tr>
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
                <h5 class="modal-title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dues.add-payment') }}">
                @csrf
                <input type="hidden" name="due_id" value="{{ $due->id }}">
                <div class="modal-body">
                    <div class="mb-3 alert alert-warning">
                        <strong>Remaining:</strong> <span class="text-danger fw-bold">৳{{ number_format($due->remaining_amount, 2) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="payment_amount" class="form-control" 
                                   max="{{ $due->remaining_amount }}" value="{{ $due->remaining_amount }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="check">Cheque</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Due Date <small class="text-muted">(if remaining balance)</small></label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction ID <small class="text-muted">(for reference)</small></label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN12345">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Record Payment
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
