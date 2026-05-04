@extends('layouts.admin')

@section('title', 'System Information')

@section('header', 'System Information')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
        <li class="breadcrumb-item active">System Info</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Users</h6>
                <h3 class="text-primary mb-0">{{ $stats['totalUsers'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Customers</h6>
                <h3 class="text-success mb-0">{{ $stats['totalCustomers'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Bills</h6>
                <h3 class="text-info mb-0">{{ $stats['totalBills'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Dues</h6>
                <h3 class="text-warning mb-0">{{ $stats['totalDues'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Pending Dues</h6>
                <h3 class="text-danger mb-0">{{ $stats['pendingDues'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Paid Dues</h6>
                <h3 class="text-success mb-0">{{ $stats['paidDues'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success">
            <div class="card-body text-center">
                <h6 class="text-white mb-1">Total Balance</h6>
                <h3 class="text-white mb-0">৳{{ number_format($stats['totalCredit'] - $stats['totalCredit'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Financial Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td>Total Credit</td>
                        <td class="text-end"><strong class="text-success">৳{{ number_format($stats['totalCredit'] ?? 0, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Total Debit</td>
                        <td class="text-end"><strong class="text-danger">৳{{ number_format($stats['totalDebit'] ?? 0, 2) }}</strong></td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Net Balance</strong></td>
                        <td class="text-end"><strong>৳{{ number_format(($stats['totalCredit'] ?? 0) - ($stats['totalDebit'] ?? 0), 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">System Overview</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td>App Name</td>
                        <td class="text-end">{{ config('app.name') }}</td>
                    </tr>
                    <tr>
                        <td>Laravel Version</td>
                        <td class="text-end">{{ app()->version() }}</td>
                    </tr>
                    <tr>
                        <td>PHP Version</td>
                        <td class="text-end">{{ phpversion() }}</td>
                    </tr>
                    <tr>
                        <td>Database</td>
                        <td class="text-end">SQLite</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection