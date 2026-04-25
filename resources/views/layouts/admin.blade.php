<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Daily Report')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <nav class="bg-dark text-white sidebar p-3" style="width: 250px; min-height: 100vh;">
            <div class="mb-4 text-center border-bottom border-secondary pb-3">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Daily Report</h5>
                <small class="text-muted">{{ Auth::user()->role === 'admin' ? 'Super Admin' : 'User' }}</small>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('customers.index') }}" class="nav-link text-white {{ request()->routeIs('customers.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-people"></i> Customers
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('bills.index') }}" class="nav-link text-white {{ request()->routeIs('bills.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-receipt"></i> Bills
                    </a>
                </li>
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link text-white {{ request()->routeIs('users.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-person-badge"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" class="nav-link text-white {{ request()->routeIs('reports.*') ? 'active bg-primary rounded' : '' }}">
                        <i class="bi bi-graph-up"></i> Reports
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
    @stack('scripts')
    @yield('scripts')
</body>
</html>