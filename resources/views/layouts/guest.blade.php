<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" href="{{ \App\Models\Setting::get('company_favicon') ? asset('storage/' . \App\Models\Setting::get('company_favicon')) : asset('favicon.ico') }}">

        <title>{{ config('app.name', 'Alam Hosiery & Store') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex">
            <!-- Left Brand Panel -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 relative overflow-hidden">
                <!-- Decorative elements -->
                <div class="absolute inset-0">
                    <div class="absolute top-20 left-20 w-72 h-72 bg-blue-500/20 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-20 right-20 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl"></div>
                    <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-cyan-400/10 rounded-full blur-2xl"></div>
                </div>

                <!-- Grid pattern overlay -->
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 40px 40px;"></div>

                <!-- Brand Content -->
                <div class="relative z-10 flex flex-col justify-between p-16 w-full">
                    <div>
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/20">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <span class="text-white/90 font-semibold text-sm tracking-wider uppercase">Daily Report System</span>
                        </div>

                        <h1 class="text-5xl font-bold text-white leading-tight mb-6">
                            Alam Hosiery<br>
                            <span class="text-blue-300">& Store</span>
                        </h1>

                        <p class="text-lg text-blue-100/70 max-w-md leading-relaxed">
                            Streamlined daily reporting and analytics platform for managing operations, tracking performance, and driving business growth.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center gap-4 p-4 bg-white/5 backdrop-blur-sm rounded-xl border border-white/10">
                            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">Real-time Analytics</p>
                                <p class="text-blue-200/60 text-xs">Track daily performance metrics</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-4 bg-white/5 backdrop-blur-sm rounded-xl border border-white/10">
                            <div class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">Secure Access</p>
                                <p class="text-blue-200/60 text-xs">Enterprise-grade security</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/10">
                            <p class="text-blue-200/40 text-xs">&copy; {{ date('Y') }} Alam Hosiery & Store. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Login Form -->
            <div class="flex-1 flex flex-col justify-center items-center bg-gray-50 px-6 py-12 lg:px-8">
                <div class="w-full max-w-md">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden mb-8 text-center">
                        <div class="inline-flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Alam Hosiery & Store</h2>
                        <p class="text-sm text-gray-500 mt-1">Daily Report System</p>
                    </div>

                    <!-- Desktop Header -->
                    <div class="hidden lg:block mb-8">
                        <h2 class="text-3xl font-semibold text-gray-900 tracking-tight">Welcome back</h2>
                        <p class="text-gray-500 mt-2 text-sm">Sign in to your account to continue</p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-6" :status="session('status')" />

                    <!-- Login Form Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/60 p-8">
                        <form method="POST" action="{{ route('login') }}" class="space-y-5">
                            @csrf

                            <!-- Email Address -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                        class="block w-full pl-11 pr-4 py-2.5 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 text-sm"
                                        placeholder="name@company.com" />
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                                            Forgot password?
                                        </a>
                                    @endif
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <input id="password" type="password" name="password" required autocomplete="current-password"
                                        class="block w-full pl-11 pr-4 py-2.5 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 text-sm"
                                        placeholder="Enter your password" />
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center">
                                <input id="remember_me" type="checkbox" name="remember"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer" />
                                <label for="remember_me" class="ml-2.5 block text-sm text-gray-600 cursor-pointer select-none">
                                    Remember me for 30 days
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm shadow-sm hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Sign in
                            </button>
                        </form>
                    </div>

                    <!-- Footer -->
                    <p class="mt-8 text-center text-xs text-gray-400 lg:hidden">
                        &copy; {{ date('Y') }} Alam Hosiery & Store
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
