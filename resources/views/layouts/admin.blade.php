<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Alam Hosiery & Store')</title>
    <link rel="icon" href="{{ \App\Models\Setting::get('company_favicon') ? asset('storage/' . \App\Models\Setting::get('company_favicon')) : asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        #managementArrow, #paymentsArrow { transition: transform 0.25s ease; }
        .sidebar-nav .nav-link { padding: 0.5rem 0.75rem; border-radius: 0.375rem; }
        .sidebar-nav .nav-link:hover { background: rgba(255,255,255,0.1); }
        .sidebar-nav .collapse .nav-link { padding-left: 0.75rem; }
        .offcanvas-sidebar { --bs-offcanvas-height: 100dvh; }
        .lang-switch { cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 0.375rem; border: 1px solid rgba(255,255,255,0.3); background: transparent; color: white; font-size: 0.8rem; transition: all 0.2s; }
        .lang-switch:hover { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.5); }
        .lang-switch.active { background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.6); }
        @media (max-width: 767.98px) {
            .sidebar-desktop { display: none !important; }
            .main-content { margin-left: 0 !important; }
        }
        @media (min-width: 768px) {
            .sidebar-mobile-toggle { display: none !important; }
            .sidebar-desktop { width: 250px; min-height: 100vh; }
        }
        .table td, .table th { white-space: nowrap; vertical-align: middle; }
        .table .text-wrap { white-space: normal; }
        @media (max-width: 575.98px) {
            .table { font-size: 0.75rem; }
            .table td, .table th { padding: 0.35rem 0.4rem !important; }
            .table .badge { font-size: 0.6rem; padding: 0.2em 0.45em; }
            .table .btn-sm { padding: 0.15rem 0.35rem; font-size: 0.65rem; }
        }
        @media (min-width: 576px) and (max-width: 767.98px) {
            .table { font-size: 0.82rem; }
            .table td, .table th { padding: 0.4rem 0.5rem !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php $currentLocale = $currentLocale ?? app()->getLocale(); @endphp
    <div class="d-flex">
        <nav class="sidebar-desktop text-white p-3 d-none d-md-flex flex-column" style="background: linear-gradient(180deg, #1a237e 0%, #283593 100%);">
            <div class="mb-4 text-center border-bottom border-secondary pb-3">
                @php $logo = \App\Models\Setting::get('company_logo'); @endphp
                @if($logo)
                <img src="{{ asset('storage/' . $logo) }}" alt="Logo" style="max-height: 40px;" class="d-block mx-auto mb-1">
                @endif
                <h5 class="mb-0"><i class="bi bi-shop"></i> Alam Hosiery</h5>
                <small class="text-white-50">& Store</small>
            </div>

            <ul class="nav flex-column sidebar-nav flex-grow-1 overflow-auto">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
                    </a>
                </li>
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('admin.today-report') }}" class="nav-link text-white {{ request()->routeIs('admin.today-report') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-calendar-check"></i> {{ __('Today Report') }}
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link text-white collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#managementMenu" role="button">
                        <span><i class="bi bi-grid-3x3-gap-fill"></i> {{ __('Management') }}</span>
                        <i class="bi bi-chevron-down small" id="managementArrow"></i>
                    </a>
                    <div class="collapse ms-3" id="managementMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{ route('customers.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('customers.*') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-people"></i> {{ __('Customers') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('banks.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('banks.*') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-bank"></i> {{ __('Banks') }}
                                </a>
                            </li>
                            @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('users.*') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-person-badge"></i> {{ __('Users') }}
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="{{ route('bills.index') }}" class="nav-link text-white {{ request()->routeIs('bills.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-receipt"></i> {{ __('Bills') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#paymentsMenu" role="button">
                        <span><i class="bi bi-cash-stack"></i> {{ __('Payments') }}</span>
                        <i class="bi bi-chevron-down small" id="paymentsArrow"></i>
                    </a>
                    <div class="collapse ms-3" id="paymentsMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{ route('dues.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('dues.*') && !request()->routeIs('dues.checks-report') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-clock-history"></i> {{ __('Dues') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('dues.checks-report') }}" class="nav-link text-white py-1 {{ request()->routeIs('dues.checks-report') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-calendar-check"></i> {{ __('Cheque Reports') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('dues.tt-report') }}" class="nav-link text-white py-1 {{ request()->routeIs('dues.tt-report') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-bank"></i> {{ __('TT Reports') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('dues.cash-report') }}" class="nav-link text-white py-1 {{ request()->routeIs('dues.cash-report') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-cash"></i> {{ __('Cash Received') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('card-payments.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('card-payments.*') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-credit-card-2-front"></i> {{ __('Reference Card') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('previous-dues.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('previous-dues.*') ? 'active bg-primary rounded' : '' }}">
                                    <i class="bi bi-clock-history"></i> {{ __('Previous Dues') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @if(!auth()->user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('user-reports.today-sales') }}" class="nav-link text-white {{ request()->routeIs('user-reports.today-sales') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-calendar-check"></i> {{ __('Today Sales Report') }}
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('imports.index') }}" class="nav-link text-white {{ request()->routeIs('imports.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-upload"></i> {{ __('Import Data') }}
                    </a>
                </li>
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" class="nav-link text-white {{ request()->routeIs('reports.*') && !request()->routeIs('reports.resources') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-graph-up"></i> {{ __('Reports') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.resources') }}" class="nav-link text-white {{ request()->routeIs('reports.resources') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-diagram-3"></i> {{ __('Resources') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.sales') }}" class="nav-link text-white {{ request()->routeIs('reports.sales') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-receipt"></i> {{ __('Sales Report') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.dues') }}" class="nav-link text-white {{ request()->routeIs('reports.dues') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-clock-history"></i> {{ __('Dues Report') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.analytics') }}" class="nav-link text-white {{ request()->routeIs('reports.analytics') ? 'active bg-info rounded' : '' }}">
                        <i class="bi bi-bar-chart-line-fill"></i> {{ __('Analytics') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('settings.index') }}" class="nav-link text-white {{ request()->routeIs('settings.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-gear"></i> {{ __('Settings') }}
                    </a>
                </li>
                @endif
            </ul>

            <div class="mt-3 border-top border-secondary pt-3">
                <div class="d-flex gap-1 mb-2">
                    <a href="{{ route('language.switch', 'en') }}" class="lang-switch {{ $currentLocale === 'en' ? 'active' : '' }}">EN</a>
                    <a href="{{ route('language.switch', 'bn') }}" class="lang-switch {{ $currentLocale === 'bn' ? 'active' : '' }}">বাং</a>
                </div>
                <a href="{{ route('profile.edit') }}" class="nav-link text-white {{ request()->routeIs('profile.edit') ? 'active bg-primary rounded' : '' }}">
                    <i class="bi bi-gear"></i> {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link text-white bg-transparent border-0 w-100 text-start">
                        <i class="bi bi-box-arrow-right"></i> {{ __('Logout') }}
                    </button>
                </form>
            </div>
        </nav>

        <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="sidebarOffcanvas">
            <div class="offcanvas-header" style="background: linear-gradient(180deg, #1a237e 0%, #283593 100%);">
                @php $logo = \App\Models\Setting::get('company_logo'); @endphp
                <div class="text-white">
                    @if($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="Logo" style="max-height: 36px;" class="d-block mb-1">
                    @endif
                    <h5 class="mb-0"><i class="bi bi-shop"></i> Alam Hosiery</h5>
                    <small class="text-white-50">& Store</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body p-0" style="background: linear-gradient(180deg, #1a237e 0%, #283593 100%);">
                <ul class="nav flex-column sidebar-nav p-2">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary rounded' : '' }}">
                            <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
                        </a>
                    </li>
                    @if(auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a href="{{ route('admin.today-report') }}" class="nav-link text-white {{ request()->routeIs('admin.today-report') ? 'active bg-primary rounded' : '' }}">
                            <i class="bi bi-calendar-check"></i> {{ __('Today Report') }}
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link text-white collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#offcanvasManagementMenu" role="button">
                            <span><i class="bi bi-grid-3x3-gap-fill"></i> {{ __('Management') }}</span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <div class="collapse ms-3" id="offcanvasManagementMenu">
                            <ul class="nav flex-column">
                                <li class="nav-item"><a href="{{ route('customers.index') }}" class="nav-link text-white py-1">{{ __('Customers') }}</a></li>
                                <li class="nav-item"><a href="{{ route('banks.index') }}" class="nav-link text-white py-1">{{ __('Banks') }}</a></li>
                                @if(auth()->user()->isAdmin())
                                <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link text-white py-1">{{ __('Users') }}</a></li>
                                @endif
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('bills.index') }}" class="nav-link text-white">
                            <i class="bi bi-receipt"></i> {{ __('Bills') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#offcanvasPaymentsMenu" role="button">
                            <span><i class="bi bi-cash-stack"></i> {{ __('Payments') }}</span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <div class="collapse ms-3" id="offcanvasPaymentsMenu">
                            <ul class="nav flex-column">
                                <li class="nav-item"><a href="{{ route('dues.index') }}" class="nav-link text-white py-1">{{ __('Dues') }}</a></li>
                                <li class="nav-item"><a href="{{ route('dues.checks-report') }}" class="nav-link text-white py-1">{{ __('Cheque Reports') }}</a></li>
                                <li class="nav-item"><a href="{{ route('dues.tt-report') }}" class="nav-link text-white py-1">{{ __('TT Reports') }}</a></li>
                                <li class="nav-item"><a href="{{ route('dues.cash-report') }}" class="nav-link text-white py-1">{{ __('Cash Received') }}</a></li>
                                <li class="nav-item"><a href="{{ route('card-payments.index') }}" class="nav-link text-white py-1">{{ __('Reference Card') }}</a></li>
                                <li class="nav-item"><a href="{{ route('previous-dues.index') }}" class="nav-link text-white py-1">{{ __('Previous Dues') }}</a></li>
                            </ul>
                        </div>
                    </li>
                    @if(!auth()->user()->isAdmin())
                    <li class="nav-item"><a href="{{ route('user-reports.today-sales') }}" class="nav-link text-white">{{ __('Today Sales Report') }}</a></li>
                    @endif
                    <li class="nav-item"><a href="{{ route('imports.index') }}" class="nav-link text-white">{{ __('Import Data') }}</a></li>
                    @if(auth()->user()->isAdmin())
                    <li class="nav-item"><a href="{{ route('reports.index') }}" class="nav-link text-white">{{ __('Reports') }}</a></li>
                    <li class="nav-item"><a href="{{ route('reports.resources') }}" class="nav-link text-white">{{ __('Resources') }}</a></li>
                    <li class="nav-item"><a href="{{ route('reports.sales') }}" class="nav-link text-white">{{ __('Sales Report') }}</a></li>
                    <li class="nav-item"><a href="{{ route('reports.dues') }}" class="nav-link text-white">{{ __('Dues Report') }}</a></li>
                    <li class="nav-item"><a href="{{ route('reports.analytics') }}" class="nav-link text-white">{{ __('Analytics') }}</a></li>
                    <li class="nav-item"><a href="{{ route('settings.index') }}" class="nav-link text-white">{{ __('Settings') }}</a></li>
                    @endif
                    <li class="nav-item mt-3 border-top border-secondary pt-3">
                        <div class="d-flex gap-1 mb-2">
                            <a href="{{ route('language.switch', 'en') }}" class="lang-switch {{ $currentLocale === 'en' ? 'active' : '' }}">EN</a>
                            <a href="{{ route('language.switch', 'bn') }}" class="lang-switch {{ $currentLocale === 'bn' ? 'active' : '' }}">বাং</a>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="nav-link text-white">{{ __('Profile') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link text-white bg-transparent border-0 w-100 text-start">{{ __('Logout') }}</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="flex-grow-1 main-content" style="min-width: 0;">
            <header class="bg-white shadow-sm p-3 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary d-md-none sidebar-mobile-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <div>
                        <h4 class="mb-0 fs-5 fs-md-4">@yield('header', __('Dashboard'))</h4>
                        @hasSection('breadcrumb')
                        <nav aria-label="breadcrumb">@yield('breadcrumb')</nav>
                        @endif
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <div class="d-flex gap-1 d-md-none">
                        <a href="{{ route('language.switch', 'en') }}" class="btn btn-sm {{ $currentLocale === 'en' ? 'btn-primary' : 'btn-outline-primary' }}">EN</a>
                        <a href="{{ route('language.switch', 'bn') }}" class="btn btn-sm {{ $currentLocale === 'bn' ? 'btn-primary' : 'btn-outline-primary' }}">বাং</a>
                    </div>
                    <span class="text-muted small d-none d-sm-inline">{{ Auth::user()->name }}</span>
                    <a href="{{ route('bills.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> <span class="d-none d-sm-inline">{{ __('New Bill') }}</span>
                    </a>
                </div>
            </header>

            <main class="p-3 p-md-4">
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
            {{ request()->routeIs('dues.*') || request()->routeIs('card-payments.*') || request()->routeIs('previous-dues.*') ? 'true' : 'false' }});
    });
    </script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
