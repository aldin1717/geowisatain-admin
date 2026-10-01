<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Grand Hotel') }} - @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-stone-900 bg-stone-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main content area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar (Mobile Menu Toggle & User Profile) -->
            <header class="bg-white border-b border-stone-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 sticky top-0">
                <div class="flex items-center lg:hidden">
                    <button @click="sidebarOpen = !sidebarOpen" type="button" class="text-stone-500 hover:text-stone-900 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="ml-4 font-semibold text-lg text-primary-700">{{ config('app.name', 'Grand Hotel') }}</span>
                </div>
                
                <div class="hidden lg:flex items-center text-lg font-medium text-stone-800">
                    @yield('title', 'Dashboard')
                </div>

                <div class="flex items-center gap-4 ml-auto">
                    <!-- User Dropdown (Alpine.js) -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-stone-700 hover:text-stone-900 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="hidden sm:block">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <!-- Dropdown menu -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-stone-100 py-1 z-50" style="display: none;">
                            <div class="px-4 py-2 border-b border-stone-100">
                                <p class="text-sm font-medium text-stone-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-stone-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">
                @if(session('success'))
                    <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
