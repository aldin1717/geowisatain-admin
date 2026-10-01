@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-stone-900">Welcome back, {{ explode(' ', Auth::user()->name)[0] }}!</h1>
        <p class="mt-1 text-sm text-stone-500">Here's what's happening at {{ config('app.name') }} today.</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        @if(Auth::user()->isAdmin() || Auth::user()->isReceptionist())
        <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-stone-500">Arrivals Today</h3>
                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                </div>
            </div>
            <div class="mt-auto">
                <span class="text-3xl font-semibold text-stone-900">12</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-stone-500">Departures Today</h3>
                <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </div>
            </div>
            <div class="mt-auto">
                <span class="text-3xl font-semibold text-stone-900">8</span>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-stone-500">Available Rooms</h3>
                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>
            <div class="mt-auto flex items-baseline gap-2">
                <span class="text-3xl font-semibold text-stone-900">24</span>
                <span class="text-sm text-stone-500 font-medium">/ 50</span>
            </div>
        </div>
        @endif

        @if(Auth::user()->isAdmin() || Auth::user()->isWarehouse())
        <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-stone-500">Low Stock Alert</h3>
                <div class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div class="mt-auto">
                <span class="text-3xl font-semibold text-stone-900">5</span>
                <span class="text-sm text-stone-500 ml-1">items</span>
            </div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Panel (Recent Activity placeholder) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden h-full">
                <div class="px-6 py-5 border-b border-stone-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-stone-900">Recent Activity</h3>
                    <button class="text-sm font-medium text-primary-600 hover:text-primary-700">View all</button>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-center h-48 text-stone-500 text-sm italic">
                        Activity logs will appear here once features are implemented.
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Panel (Quick Actions placeholder) -->
        <div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden h-full">
                <div class="px-6 py-5 border-b border-stone-200">
                    <h3 class="text-base font-semibold text-stone-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    @if(Auth::user()->isAdmin() || Auth::user()->isReceptionist())
                    <button class="w-full flex items-center gap-3 px-4 py-2.5 bg-stone-50 hover:bg-stone-100 text-stone-700 rounded-lg transition-colors text-left border border-stone-200">
                        <svg class="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span class="font-medium text-sm">New Booking</span>
                    </button>
                    @endif
                    
                    @if(Auth::user()->isAdmin() || Auth::user()->isWarehouse())
                    <button class="w-full flex items-center gap-3 px-4 py-2.5 bg-stone-50 hover:bg-stone-100 text-stone-700 rounded-lg transition-colors text-left border border-stone-200">
                        <svg class="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="font-medium text-sm">Receive Stock</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
