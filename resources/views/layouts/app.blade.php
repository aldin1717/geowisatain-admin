<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ config('app.name', 'Geowisata Inn') }} - @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-[#f8f9fa]" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main content area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar (Mobile Menu Toggle & User Profile) -->
            <header class="bg-[#ffffff] border-b border-[#e2e8f0] h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 sticky top-0">
                <div class="flex items-center lg:hidden">
                    <button @click="sidebarOpen = !sidebarOpen" type="button" class="text-slate-500 hover:text-slate-800 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="ml-4 font-semibold text-lg text-primary-700">{{ config('app.name', 'Geowisata Inn') }}</span>
                </div>
                
                <div class="hidden lg:flex items-center text-lg font-medium text-slate-800">
                    @yield('title', 'Dashboard')
                </div>

                <div class="flex items-center gap-4 ml-auto">
                    <!-- User Dropdown (Alpine.js) -->
                    <div class="relative" x-data="{ open: false, logoutConfirmOpen: false, confirmLogout() { this.$refs.logoutForm.requestSubmit(); } }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="hidden sm:block">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <!-- Dropdown menu -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-[#e2e8f0] py-1 z-50" style="display: none;">
                            <div class="px-4 py-2 border-b border-[#e2e8f0]">
                                <p class="text-sm font-medium text-slate-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" x-ref="logoutForm">
                                @csrf
                                <button type="button" @click="open = false; logoutConfirmOpen = true" class="block w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    Sign out
                                </button>
                            </form>
                        </div>
                        <div x-show="logoutConfirmOpen" x-transition.opacity class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/50 p-4" role="presentation" @click.self="logoutConfirmOpen = false" @keydown.escape.window="logoutConfirmOpen = false" style="display: none;">
                            <div x-show="logoutConfirmOpen" x-transition class="w-full max-w-md rounded-2xl border border-stone-200 bg-white p-6 text-center shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="logout-confirmation-title" aria-describedby="logout-confirmation-description">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary-50 text-primary-700">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 17l5-5-5-5m5 5H3m9 4v1a3 3 0 003 3h4a2 2 0 002-2V6a2 2 0 00-2-2h-4a3 3 0 00-3 3v1"></path></svg>
                                </div>
                                <h2 id="logout-confirmation-title" class="mt-4 text-lg font-semibold text-stone-900">Konfirmasi Logout</h2>
                                <p id="logout-confirmation-description" class="mt-2 text-sm leading-6 text-stone-600">Apakah Anda yakin ingin keluar dari akun?</p>
                                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
                                    <button type="button" @click="logoutConfirmOpen = false" class="inline-flex justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-medium text-stone-700 transition hover:bg-stone-50">
                                        Batal
                                    </button>
                                    <button type="button" @click="confirmLogout()" class="inline-flex justify-center rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-primary-800">
                                        Ya, logout
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.opacity class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/40 p-4" role="presentation" @click.self="show = false" style="display: none;">
                        <div x-show="show" x-transition class="w-full max-w-md rounded-2xl border border-emerald-200 bg-white p-6 text-center shadow-2xl" role="alert" aria-live="polite">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h2 class="mt-4 text-lg font-semibold text-stone-900">Berhasil</h2>
                            <p class="mt-2 text-sm leading-6 text-stone-600">{{ session('success') }}</p>
                            <button type="button" @click="show = false" class="mt-6 inline-flex justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-primary-800">
                                Tutup
                            </button>
                        </div>
                    </div>
                @endif
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.opacity class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/40 p-4" role="presentation" @click.self="show = false" style="display: none;">
                        <div x-show="show" x-transition class="w-full max-w-md rounded-2xl border border-red-200 bg-white p-6 text-center shadow-2xl" role="alert" aria-live="assertive">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-600">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <h2 class="mt-4 text-lg font-semibold text-stone-900">Gagal</h2>
                            <p class="mt-2 text-sm leading-6 text-stone-600">{{ session('error') }}</p>
                            <button type="button" @click="show = false" class="mt-6 inline-flex justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-primary-800">
                                Tutup
                            </button>
                        </div>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
