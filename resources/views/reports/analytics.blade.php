@extends('layouts.admin')

@section('title', __('Data Analytics'))
@section('header', __('Data Analytics'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">{{ __('Reports') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Analytics') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h4>{{ __('Business Intelligence') }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.analytics', ['period' => 7]) }}" class="btn btn-sm btn-outline-secondary {{ $period == 7 ? 'active' : '' }}">{{ __('7 Days') }}</a>
        <a href="{{ route('reports.analytics', ['period' => 30]) }}" class="btn btn-sm btn-outline-secondary {{ $period == 30 ? 'active' : '' }}">{{ __('30 Days') }}</a>
        <a href="{{ route('reports.analytics', ['period' => 90]) }}" class="btn btn-sm btn-outline-secondary {{ $period == 90 ? 'active' : '' }}">{{ __('90 Days') }}</a>
        <a href="{{ route('reports.analytics', ['period' => 365]) }}" class="btn btn-sm btn-outline-secondary {{ $period == 365 ? 'active' : '' }}">{{ __('1 Year') }}</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">{{ __('Net Revenue') }}</p>
                        <h3 class="mb-0 fw-bold">{{ format_currency($netSales) }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-2 rounded">
                        <i class="bi bi-graph-up text-primary"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-{{ $salesGrowth >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $salesGrowth >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-arrow-{{ $salesGrowth >= 0 ? 'up' : 'down' }}"></i>
                        {{ format_number(abs($salesGrowth), 1) }}%
                    </span>
                    <small class="text-muted">{{ __('vs previous period') }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">{{ __('Total Bills') }}</p>
                        <h3 class="mb-0 fw-bold">{{ $billCount }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-2 rounded">
                        <i class="bi bi-receipt text-info"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-{{ $billCountGrowth >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $billCountGrowth >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-arrow-{{ $billCountGrowth >= 0 ? 'up' : 'down' }}"></i>
                        {{ format_number(abs($billCountGrowth), 1) }}%
                    </span>
                    <small class="text-muted">{{ __('vs previous period') }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">{{ __('Avg Bill Value') }}</p>
                        <h3 class="mb-0 fw-bold">{{ format_currency($avgBillValue) }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-2 rounded">
                        <i class="bi bi-calculator text-warning"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-{{ $avgBillGrowth >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $avgBillGrowth >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-arrow-{{ $avgBillGrowth >= 0 ? 'up' : 'down' }}"></i>
                        {{ format_number(abs($avgBillGrowth), 1) }}%
                    </span>
                    <small class="text-muted">{{ __('vs previous period') }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">{{ __('Collection Rate') }}</p>
                        <h3 class="mb-0 fw-bold">{{ format_number($collectionRate, 1) }}%</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-2 rounded">
                        <i class="bi bi-shield-check text-success"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $collectionRate }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">{{ __('Revenue Trend') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">{{ __('Payment Distribution') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="paymentChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">{{ __('Top Customers') }}</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Customer') }}</th>
                            <th class="text-end">{{ __('Total') }}</th>
                            <th class="text-end">{{ __('Bills') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCustomers as $index => $customer)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $index + 1 }}</span></td>
                            <td>{{ $customer->customer->name ?? __('Unknown') }}</td>
                            <td class="text-end fw-bold">{{ format_currency($customer->total) }}</td>
                            <td class="text-end">{{ $customer->count }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3">{{ __('No data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">{{ __('Outstanding & Risk') }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-danger bg-opacity-10 rounded">
                            <small class="text-muted d-block">{{ __('Pending Dues') }}</small>
                            <h5 class="mb-0 text-danger fw-bold">{{ format_currency($dueStats['total_pending']) }}</h5>
                            <small class="text-muted">{{ $dueStats['pending_count'] }} {{ __('records') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-warning bg-opacity-10 rounded">
                            <small class="text-muted d-block">{{ __('Overdue Dues') }}</small>
                            <h5 class="mb-0 text-warning fw-bold">{{ format_currency($overdueDues) }}</h5>
                            <small class="text-muted">{{ __('Past due date') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-info bg-opacity-10 rounded">
                            <small class="text-muted d-block">{{ __('Pending Cheques') }}</small>
                            <h5 class="mb-0 text-info fw-bold">{{ format_currency($checkPending) }}</h5>
                            <small class="text-muted">{{ __('Awaiting encash') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-success bg-opacity-10 rounded">
                            <small class="text-muted d-block">{{ __('Recovery Rate') }}</small>
                            <h5 class="mb-0 text-success fw-bold">{{ format_number($recoveryRate, 1) }}%</h5>
                            <small class="text-muted">{{ __('Due collection') }}</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ __('Due Recovery Progress') }}</span>
                        <span class="fw-bold">{{ format_number($recoveryRate, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: {{ $recoveryRate }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->isAdmin() && count($userPerformance) > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2 py-md-3 px-3 px-md-4">
                <h6 class="mb-0 fw-bold fs-6 fs-md-5">{{ __('Team Performance') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Rank') }}</th>
                                <th>{{ __('User') }}</th>
                                <th class="text-end">{{ __('Total Sales') }}</th>
                                <th class="text-end">{{ __('Bills') }}</th>
                                <th class="text-end d-none d-sm-table-cell">{{ __('Discount') }}</th>
                                <th class="text-end d-none d-md-table-cell">{{ __('Rep Disc') }}</th>
                                <th class="text-end d-none d-md-table-cell">{{ __('Total Disc') }}</th>
                                <th class="text-end">{{ __('Avg Bill') }}</th>
                                <th class="d-none d-lg-table-cell" style="width: 150px;">{{ __('Performance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userPerformance as $index => $user)
                            <tr>
                                <td><span class="badge bg-{{ $index === 0 ? 'warning text-dark' : 'secondary' }}">#{{ $index + 1 }}</span></td>
                                <td class="fw-semibold">{{ $user['name'] }}</td>
                                <td class="text-end fw-bold">{{ format_currency($user['sales']) }}</td>
                                <td class="text-end">{{ $user['bills'] }}</td>
                                <td class="text-end text-danger d-none d-sm-table-cell">{{ format_currency($user['discount']) }}</td>
                                <td class="text-end text-danger d-none d-md-table-cell">{{ format_currency($user['report_discount'] ?? 0) }}</td>
                                <td class="text-end fw-bold text-danger d-none d-md-table-cell">{{ format_currency($user['discount'] + ($user['report_discount'] ?? 0)) }}</td>
                                <td class="text-end">{{ format_currency($user['bills'] > 0 ? $user['sales'] / $user['bills'] : 0) }}</td>
                                <td class="d-none d-lg-table-cell">
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

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">{{ __('Daily Transaction Volume') }}</h6>
            </div>
            <div class="card-body">
                <canvas id="volumeChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">{{ __('Key Insights') }}</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @php
                        $topPayment = collect($paymentTypes)->sortByDesc('total')->first();
                        $topPaymentType = collect($paymentTypes)->keys()->first(fn($key) => $paymentTypes[$key]['total'] === max(array_column($paymentTypes, 'total')));
                    @endphp
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-trophy-fill text-warning me-2"></i>{{ __('Top Payment Method') }}</span>
                        <span class="fw-bold badge bg-primary">{{ strtoupper($topPaymentType) }} ({{ format_currency($paymentTypes[$topPaymentType]['total']) }})</span>
                    </li>
                    @php $combinedDiscount = $totalDiscount + ($totalReportDiscount ?? 0); @endphp
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-graph-down-arrow text-danger me-2"></i>{{ __('Bill Discount') }}</span>
                        <span class="fw-bold text-danger">{{ format_currency($totalDiscount) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-graph-down-arrow text-danger me-2"></i>{{ __('Report Discount') }}</span>
                        <span class="fw-bold text-danger">{{ format_currency($totalReportDiscount ?? 0) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-graph-down-arrow text-danger me-2"></i>{{ __('Total Discount') }}</span>
                        <span class="fw-bold text-danger">{{ format_currency($combinedDiscount) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-clock-history text-info me-2"></i>{{ __('Avg Bills/Day') }}</span>
                        <span class="fw-bold">{{ format_number($billCount / max($period, 1), 1) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-percent text-success me-2"></i>{{ __('Discount Rate') }}</span>
                        <span class="fw-bold">{{ $totalSales > 0 ? format_number(($combinedDiscount / $totalSales) * 100, 1) : 0 }}%</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-piggy-bank text-primary me-2"></i>{{ __('Due Collected') }}</span>
                        <span class="fw-bold text-success">{{ format_currency($dueCollection) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim() || '#0d6efd';
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-success').trim() || '#198754';
    const warningColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-warning').trim() || '#ffc107';
    const infoColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-info').trim() || '#0dcaf0';
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-danger').trim() || '#dc3545';

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: @json($dailyLabels),
            datasets: [{
                label: 'Revenue',
                data: @json($dailyValues),
                borderColor: primaryColor,
                backgroundColor: primaryColor + '20',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
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

    const paymentData = @json($paymentTypes);
    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Cheque', 'TT', 'Reference Card', 'Due'],
            datasets: [{
                data: [paymentData.cash.total, paymentData.check.total, paymentData.tt.total, paymentData.card.total, paymentData.due.total],
                backgroundColor: [successColor, warningColor, infoColor, '#6c757d', dangerColor],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } }
            },
            cutout: '65%'
        }
    });

    new Chart(document.getElementById('volumeChart'), {
        type: 'bar',
        data: {
            labels: @json($dailyLabels),
            datasets: [{
                label: 'Bills',
                data: @json($dailyCounts),
                backgroundColor: primaryColor + '80',
                borderColor: primaryColor,
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endsection
