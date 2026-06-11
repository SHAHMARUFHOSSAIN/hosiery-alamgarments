<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Alam Hosiery & Store')</title>
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\Setting::get('company_favicon') ? asset('storage/' . \App\Models\Setting::get('company_favicon')) : asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        #managementArrow, #paymentsArrow { transition: transform 0.25s ease; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <nav class="sidebar text-white sidebar p-3" style="width: 250px; min-height: 100vh; background: linear-gradient(180deg, #1a237e 0%, #283593 100%);">
            <div class="mb-4 text-center border-bottom border-secondary pb-3">
                @php $logo = \App\Models\Setting::get('company_logo'); @endphp
                @if($logo)
                <img src="{{ asset('storage/' . $logo) }}" alt="Logo" style="max-height: 40px;" class="d-block mx-auto mb-1">
                @endif
                <h5 class="mb-0"><i class="bi bi-shop"></i> Alam Hosiery</h5>
                <small class="text-white-50">& Store</small>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#managementMenu" role="button">
                        <span><i class="bi bi-grid-3x3-gap-fill"></i> Management</span>
                        <i class="bi bi-chevron-down small" id="managementArrow"></i>
                    </a>
                    <div class="collapse ms-3" id="managementMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{ route('customers.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('customers.*') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-people"></i> Customers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('banks.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('banks.*') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-bank"></i> Banks
                                </a>
                            </li>
                            @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('users.*') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-person-badge"></i> Users
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="{{ route('bills.index') }}" class="nav-link text-white {{ request()->routeIs('bills.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-receipt"></i> Bills
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#paymentsMenu" role="button">
                        <span><i class="bi bi-cash-stack"></i> Payments</span>
                        <i class="bi bi-chevron-down small" id="paymentsArrow"></i>
                    </a>
                    <div class="collapse ms-3" id="paymentsMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{ route('dues.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('dues.*') && !request()->routeIs('dues.checks-report') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-clock-history"></i> Dues
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('dues.checks-report') }}" class="nav-link text-white py-1 {{ request()->routeIs('dues.checks-report') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-calendar-check"></i> Cheque Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('card-payments.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('card-payments.*') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-credit-card-2-front"></i> Reference Card
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('main-balance.index') }}" class="nav-link text-white {{ request()->routeIs('main-balance.*') || request()->routeIs('user-balance.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-wallet2"></i> Balance
                    </a>
                </li>
                @else
                <li class="nav-item">
                    <a href="{{ route('user-balance.index') }}" class="nav-link text-white {{ request()->routeIs('user-balance.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-wallet2"></i> My Balance
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('imports.index') }}" class="nav-link text-white {{ request()->routeIs('imports.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-upload"></i> Import Data
                    </a>
                </li>
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" class="nav-link text-white {{ request()->routeIs('reports.*') && !request()->routeIs('reports.analytics') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-graph-up"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.analytics') }}" class="nav-link text-white {{ request()->routeIs('reports.analytics') ? 'active bg-info rounded' : '' }}">
                        <i class="bi bi-bar-chart-line-fill"></i> Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('settings.index') }}" class="nav-link text-white {{ request()->routeIs('settings.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>
                @endif
            </ul>

            <div class="mt-4 border-top border-secondary pt-3">
                <a href="{{ route('profile.edit') }}" class="nav-link text-white {{ request()->routeIs('profile.edit') ? 'active bg-primary rounded' : '' }}">
                    <i class="bi bi-gear"></i> Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link text-white bg-transparent border-0 w-100 text-start">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        <div class="flex-grow-1">
            <header class="bg-white shadow-sm p-3 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">@yield('header', 'Dashboard')</h4>
                    @hasSection('breadcrumb')
                    <nav aria-label="breadcrumb">@yield('breadcrumb')</nav>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted">{{ Auth::user()->name }}</span>
                    <a href="{{ route('bills.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> New Bill
                    </a>
                </div>
            </header>

            <main class="p-4">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function setupCollapse(id, arrowId, active) {
            var el = document.getElementById(id);
            if (!el) return;
            if (active) new bootstrap.Collapse(el, { show: true });
            el.addEventListener('show.bs.collapse', function () {
                var arrow = document.getElementById(arrowId);
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            });
            el.addEventListener('hide.bs.collapse', function () {
                var arrow = document.getElementById(arrowId);
                if (arrow) arrow.style.transform = '';
            });
        }
        setupCollapse('managementMenu', 'managementArrow',
            {{ request()->routeIs('customers.*') || request()->routeIs('banks.*') || request()->routeIs('users.*') ? 'true' : 'false' }});
        setupCollapse('paymentsMenu', 'paymentsArrow',
            {{ request()->routeIs('dues.*') || request()->routeIs('card-payments.*') ? 'true' : 'false' }});
    });
    </script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>