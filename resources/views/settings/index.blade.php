@extends('layouts.admin')

@section('title', __('Settings'))

@section('header', __('Settings'))

@section('content')
<form method="GET" class="mb-4">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <label class="me-2">{{ __('Time Period:') }}</label>
        <select name="days" class="form-select" style="width: auto;" onchange="this.form.submit()">
            <option value="7" {{ $days == 7 ? 'selected' : '' }}>{{ __('Last 7 Days') }}</option>
            <option value="30" {{ $days == 30 ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
            <option value="90" {{ $days == 90 ? 'selected' : '' }}>{{ __('Last 90 Days') }}</option>
            <option value="365" {{ $days == 365 ? 'selected' : '' }}>{{ __('Last 1 Year') }}</option>
            <option value="0" {{ $days == 0 ? 'selected' : '' }}>{{ __('All Time') }}</option>
        </select>
    </div>
</form>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Users') }}</h6>
                <h3 class="text-primary mb-0">{{ $stats['totalUsers'] }}</h3>
                @if($stats['recentUsers'] > 0)
                <small class="text-success">+{{ $stats['recentUsers'] }} {{ __('this period') }}</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Customers') }}</h6>
                <h3 class="text-success mb-0">{{ $stats['totalCustomers'] }}</h3>
                @if($stats['recentCustomers'] > 0)
                <small class="text-success">+{{ $stats['recentCustomers'] }} {{ __('this period') }}</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Bills') }}</h6>
                <h3 class="text-info mb-0">{{ $stats['totalBills'] }}</h3>
                @if($stats['recentBills'] > 0)
                <small class="text-success">+{{ $stats['recentBills'] }} {{ __('this period') }}</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Pending Dues') }}</h6>
                <h3 class="text-warning mb-0">{{ $stats['totalDues'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-people text-primary fs-2"></i>
            </div>
            <h5>{{ __('User Management') }}</h5>
            <p class="text-muted">{{ __('Manage all users, create new users, edit roles and permissions') }}</p>
            <a href="{{ route('settings.users') }}" class="btn btn-primary">{{ __('Manage Users') }}</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-database text-info fs-2"></i>
            </div>
            <h5>{{ __('Data Management') }}</h5>
            <p class="text-muted">{{ __('View, edit and delete all data across the system') }}</p>
            <a href="{{ route('settings.data') }}" class="btn btn-info">{{ __('Manage Data') }}</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <div class="bg-secondary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                <i class="bi bi-building text-secondary fs-2"></i>
            </div>
            <h5>{{ __('Company Info') }}</h5>
            <p class="text-muted">{{ __('Update company name, address, phone and email') }}</p>
            <a href="{{ route('settings.company') }}" class="btn btn-secondary">{{ __('Edit Company') }}</a>
        </div>
    </div>
</div>

<div class="row g-4 mt-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('settings.users') }}" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus"></i> {{ __('Add New User') }}
                    </a>
                    <a href="{{ route('settings.data') }}" class="btn btn-outline-success">
                        <i class="bi bi-database"></i> {{ __('Manage All Data') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('System Revenue') }}</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-success mb-2">{{ format_currency($stats['totalRevenue'] ?? 0) }}</h2>
                <p class="text-muted">{{ __('Total Credit Revenue') }}</p>
                @if($stats['recentRevenue'] > 0)
                <hr>
                <p class="text-success mb-1">+ {{ format_currency($stats['recentRevenue']) }} {{ __('this period') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
