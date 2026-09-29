@extends('layouts.admin')

@section('title', __('Product Management'))

@section('header', __('Product Management'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Products') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="mb-0">{{ __('Product Management') }}</h2>
        <div class="btn-group btn-group-sm mt-2" role="group" aria-label="Product navigation">
            <a href="{{ route('products.catalog') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul"></i> {{ __('Product List') }}
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary active">
                <i class="bi bi-graph-up"></i> {{ __('Sales Reports') }}
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="period" value="{{ $period }}">
            <div class="col-md-3">
                <label class="form-label mb-1">{{ __('Category') }}</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach(\App\Models\Product::categories() as $key => $label)
                    <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-9 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> {{ __('Filter') }}</button>
                <a href="{{ route('products.index', ['period' => $period]) }}" class="btn btn-sm btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> {{ __('Top 10 Selling Products') }}</h5>
                <div class="btn-group btn-group-sm" role="group">
                    @php
                    $periods = [
                        'all' => __('All Time'),
                        'today' => __('Today'),
                        'week' => __('This Week'),
                        'month' => __('This Month'),
                        'year' => __('This Year'),
                    ];
                    $currentPeriod = $period ?? 'all';
                    @endphp
                    @foreach($periods as $key => $label)
                    <a href="{{ route('products.index', array_filter(['category' => $category, 'period' => $key])) }}"
                       class="btn btn-outline-primary {{ $currentPeriod == $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 8%;">#</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Size') }}</th>
                            <th class="text-end">{{ __('Quantity') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $index => $tp)
                        <tr>
                            <td class="fw-bold">
                                @if($index < 3)
                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> {{ $index + 1 }}</span>
                                @else
                                {{ $index + 1 }}
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $tp->product_name }}</td>
                            <td>{{ __(\App\Models\Product::categoryLabel($tp->category) ?? '—') }}</td>
                            <td>{{ $tp->size_name ?? '—' }}</td>
                            <td class="text-end">{{ format_number($tp->total_qty, 2) }}</td>
                            <td class="text-end">{{ format_currency($tp->total_amount) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-3">{{ __('No products sold yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> {{ __('User-wise Product Sales') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th class="text-end">{{ __('Items Sold') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                            <th class="text-end">{{ __('Bills') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userWise as $uw)
                        <tr>
                            <td class="fw-semibold">{{ $uw->user_name }}</td>
                            <td class="text-end">{{ format_number($uw->total_qty, 2) }}</td>
                            <td class="text-end">{{ format_currency($uw->total_amount) }}</td>
                            <td class="text-end">{{ $uw->bill_count }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3">{{ __('No products sold yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-diagram-3"></i> {{ __('Category-wise Product Sales') }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Category') }}</th>
                    <th class="text-end">{{ __('Products') }}</th>
                    <th class="text-end">{{ __('Quantity') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Bills') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categoryWise as $cw)
                <tr>
                    <td class="fw-semibold">{{ __(\App\Models\Product::categoryLabel($cw->category) ?? 'Uncategorized') }}</td>
                    <td class="text-end">{{ $cw->product_count }}</td>
                    <td class="text-end">{{ format_number($cw->total_qty, 2) }}</td>
                    <td class="text-end">{{ format_currency($cw->total_amount) }}</td>
                    <td class="text-end">{{ $cw->bill_count }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-3">{{ __('No products sold yet') }}</td></tr>
                @endforelse
            </tbody>
            @if($categoryWise->isNotEmpty())
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="2" class="text-end">{{ __('Total') }}:</td>
                    <td class="text-end">{{ format_number($categoryWise->sum('total_qty'), 2) }}</td>
                    <td class="text-end">{{ format_currency($categoryWise->sum('total_amount')) }}</td>
                    <td class="text-end">{{ $categoryWise->sum('bill_count') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection