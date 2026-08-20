@php
    $isAdmin = auth()->user()->isAdmin();
@endphp

@extends('layouts.admin')

@section('title', __('Admin Dashboard'))

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

<div class="row g-3 g-md-4 mb-4">
    <div class="col-6 col-md">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-people text-primary fs-2"></i>
                </div>
                <h3 class="mb-1">{{ format_number($stats['totalCustomers']) }}</h3>
                <p class="text-muted mb-0">{{ __('Total Customers') }}</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-receipt text-success fs-2"></i>
                </div>
                <h3 class="mb-1">{{ format_number($stats['totalBills']) }}</h3>
                <p class="text-muted mb-0">{{ __('Total Bills') }}</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-currency-dollar text-danger fs-2"></i>
                </div>
                <h3 class="mb-1 text-danger">{{ format_number($stats['totalDues'], 2) }}</h3>
                <p class="text-muted mb-0">{{ __('Pending Dues') }}</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-calendar-event text-warning fs-2"></i>
                </div>
                <h3 class="mb-1">{{ format_number($stats['todayDues']) }}</h3>
                <p class="text-muted mb-0">{{ __('Due Today') }}</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-2">
                    <i class="bi bi-graph-up text-info fs-2"></i>
                </div>
                <h3 class="mb-1 text-info">{{ format_currency($stats['totalSales']) }}</h3>
                <p class="text-muted mb-0">
                    {{ __('Total Sales') }}
                    @if($dateFrom || $dateTo)
                    <small class="d-block text-muted">{{ $dateFrom }} {{ __('to') }} {{ $dateTo }}</small>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>{{ __('Filter by Date') }}</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">{{ __('Date From') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">{{ __('Date To') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>{{ __('Filter') }}
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>
</div>

@if(count($userStats) > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>{{ __('User Performance Overview') }} <small class="text-muted fs-6 fw-normal">({{ $dateFrom }} {{ __('to') }} {{ $dateTo }})</small></h5>
        <span class="badge bg-primary fs-6">{{ count($userStats) }} {{ __('users') }}</span>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            @foreach($userStats as $user)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card border h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0">{{ $user['name'] }}</h6>
                            @if($user['todayDues'] > 0)
                            <span class="badge bg-warning text-dark">{{ $user['todayDues'] }} {{ __('due') }}</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">{{ __('Customers') }}</span>
                            <span class="fw-semibold">{{ format_number($user['customers']) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">{{ __('Bills') }}</span>
                            <span class="fw-semibold">{{ format_number($user['bills']) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">{{ __('Sales') }}</span>
                            <span class="fw-semibold text-primary">{{ format_currency($user['sales']) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">{{ __('Pending Dues') }}</span>
                            <span class="fw-semibold text-danger">{{ format_currency($user['dues']) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>{{ __('Comparison Performance Chart') }} <small class="text-muted fs-6 fw-normal">({{ $dateFrom }} {{ __('to') }} {{ $dateTo }})</small></h5>
            </div>
            <div class="card-body">
                <canvas id="userComparisonChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

@if(count($userPerformance) > 0)
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>{{ __('Team Performance') }} <small class="text-muted fs-6 fw-normal">({{ $dateFrom }} {{ __('to') }} {{ $dateTo }})</small></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Rank') }}</th>
                                <th>{{ __('User') }}</th>
                                <th class="text-end">{{ __('Total Sales') }}</th>
                                <th class="text-end">{{ __('Bills') }}</th>
                                <th class="text-end">{{ __('Discount') }}</th>
                                <th class="text-end">{{ __('Rep Discount') }}</th>
                                <th class="text-end">{{ __('Total Disc') }}</th>
                                <th class="text-end">{{ __('Avg Bill') }}</th>
                                <th>{{ __('Performance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userPerformance as $index => $user)
                            <tr>
                                <td><span class="badge bg-{{ $index === 0 ? 'warning text-dark' : 'secondary' }}">#{{ $index + 1 }}</span></td>
                                <td class="fw-semibold">{{ $user['name'] }}</td>
                                <td class="text-end fw-bold">{{ format_currency($user['sales']) }}</td>
                                <td class="text-end">{{ $user['bills'] }}</td>
                                <td class="text-end text-danger">{{ format_currency($user['discount']) }}</td>
                                <td class="text-end text-danger">{{ format_currency($user['report_discount'] ?? 0) }}</td>
                                <td class="text-end fw-bold text-danger">{{ format_currency($user['discount'] + ($user['report_discount'] ?? 0)) }}</td>
                                <td class="text-end">{{ format_currency($user['bills'] > 0 ? $user['sales'] / $user['bills'] : 0) }}</td>
                                <td style="width: 200px;">
                                    @php
                                        $maxSales = max(array_column($userPerformance, 'sales'));
                                        $perf = $maxSales > 0 ? ($user['sales'] / $maxSales) * 100 : 0;
                                    @endphp
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $perf }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Recent Bills') }}</h5>
                <a href="{{ route('bills.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Bill No') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('User') }}</th>
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
                            <td>{{ format_number($bill->bill_amount, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ $bill->user->name ?? 'N/A' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3">{{ __('No bills found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Pending Dues') }}</h5>
                <a href="{{ route('dues.daily-report') }}" class="btn btn-sm btn-warning">{{ __('Today') }}</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Original') }}</th>
                            <th>{{ __('Remaining') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDues as $due)
                        <tr>
                            <td>{{ $due->customer->name ?? 'N/A' }}</td>
                            <td>{{ format_number($due->original_amount, 2) }}</td>
                            <td class="text-danger fw-bold">{{ format_number($due->remaining_amount, 2) }}</td>
                            <td><span class="badge bg-warning text-dark">{{ $due->due_date->format('M d') }}</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#dashPayModal{{ $due->id }}">
                                    <i class="bi bi-credit-card"></i> {{ __('Pay') }}
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3">{{ __('No pending dues') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-warning"></i>{{ __('Pending Cheques') }}</h5>
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
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Cheque Date') }}</th>
                            <th>{{ __('User') }}</th>
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
                            <td class="fw-bold">{{ format_currency($remaining) }}</td>
                            <td>{{ $cheque->check_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td><span class="badge bg-secondary">{{ $cheque->bill->user->name ?? 'N/A' }}</span></td>
                            <td>
                                <a href="{{ route('bills.show', $cheque->bill) }}" class="btn btn-sm btn-success py-0 px-2">
                                    <i class="bi bi-credit-card"></i> {{ __('Encash') }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-3 text-muted">{{ __('No pending cheques') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($recentDues as $due)
<div class="modal fade" id="dashPayModal{{ $due->id }}" tabindex="-1">
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

@if(count($userStats) > 0)
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim() || '#0d6efd';
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-success').trim() || '#198754';
    const warningColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-warning').trim() || '#ffc107';
    const infoColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-info').trim() || '#0dcaf0';

    new Chart(document.getElementById('userComparisonChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Sales (৳)',
                    data: @json($chartSales),
                    backgroundColor: primaryColor + '90',
                    borderColor: primaryColor,
                    borderWidth: 1,
                    borderRadius: 3,
                    order: 1,
                },
                {
                    label: 'Bills',
                    data: @json($chartBills),
                    backgroundColor: successColor + '90',
                    borderColor: successColor,
                    borderWidth: 1,
                    borderRadius: 3,
                    order: 2,
                },
                {
                    label: 'Pending Dues (৳)',
                    data: @json($chartDues),
                    backgroundColor: warningColor + '90',
                    borderColor: warningColor,
                    borderWidth: 1,
                    borderRadius: 3,
                    order: 3,
                },
                {
                    label: 'Customers',
                    data: @json($chartCustomers),
                    backgroundColor: infoColor + '90',
                    borderColor: infoColor,
                    borderWidth: 1,
                    borderRadius: 3,
                    order: 4,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, padding: 15 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '৳' + v.toLocaleString() }
                }
            }
        }
    });
});
</script>
@endsection
@endif
