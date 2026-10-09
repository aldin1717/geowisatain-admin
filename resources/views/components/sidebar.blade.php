<!-- Mobile sidebar backdrop -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-stone-900/80 z-20 lg:hidden" 
     @click="sidebarOpen = false" 
     style="display: none;">
</div>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
       class="fixed inset-y-0 left-0 z-30 w-64 bg-[#0f172a] text-slate-300 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 shadow-xl flex flex-col">
    
    <!-- Logo -->
    <div class="h-16 flex items-center px-6 bg-[#111c2b] border-b border-[#334155]">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-primary-500 flex items-center justify-center text-white shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <span class="font-bold text-lg text-white tracking-wide">{{ config('app.name', 'GeowisataInn') }}</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-8 overflow-y-auto custom-scrollbar">
        
        <div>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="font-medium">Dashboard</span>
            </a>
        </div>

        @if(Auth::user()->isAdmin() || Auth::user()->isReceptionist())
        <div>
            <p class="px-3 text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">Hotel Operations</p>
            <div class="space-y-1">
                <a href="{{ route('bookings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('bookings.*') && !request()->has('status') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Bookings</span>
                </a>
                <a href="{{ route('billing-groups.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('billing-groups.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m-7-7h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2zm2 0V3h4v2"></path></svg>
                    <span>Tagihan Gabungan</span>
                </a>
                <a href="{{ route('bookings.index', ['status' => 'confirmed']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request('status') === 'confirmed' ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span>Check-in</span>
                </a>
                <a href="{{ route('bookings.index', ['status' => 'checked_in']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request('status') === 'checked_in' ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span>Check-out</span>
                </a>
                <a href="{{ route('guests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('guests.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Guests</span>
                </a>
                <a href="{{ route('room-types.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('room-types.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>Room Types</span>
                </a>
                <a href="{{ route('rooms.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('rooms.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Rooms</span>
                </a>
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('reports.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M18 17V9m-5 8V5m-5 12v-5"></path></svg>
                    <span>Reports</span>
                </a>
                <a href="{{ route('shifts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('shifts.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"></path></svg>
                    <span>Rekap Shift</span>
                </a>
            </div>
        </div>
        @endif

        @if(Auth::user()->isAdmin() || Auth::user()->isWarehouse())
        <div>
            <p class="px-3 text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">Inventory</p>
            <div class="space-y-1">
                <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('inventory.index', 'inventory.items.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Items</span>
                </a>
                <a href="{{ route('inventory.categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('inventory.categories.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    <span>Categories</span>
                </a>
                <a href="{{ route('inventory.transactions.create', ['type' => 'stock_in']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request('type') === 'stock_in' && request()->routeIs('inventory.transactions.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z"></path></svg>
                    <span>Stock In</span>
                </a>
                <a href="{{ route('inventory.transactions.create', ['type' => 'stock_out']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request('type') === 'stock_out' && request()->routeIs('inventory.transactions.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                    <span>Stock Out</span>
                </a>
                <a href="{{ route('inventory.transactions.create', ['type' => 'adjustment']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request('type') === 'adjustment' && request()->routeIs('inventory.transactions.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 19a8 8 0 0013.7-3M19 5a8 8 0 00-13.7 3"></path></svg>
                    <span>Stock Adjustment</span>
                </a>
                <a href="{{ route('inventory.transactions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('inventory.transactions.index') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-6 4h6m-6 4h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"></path></svg>
                    <span>Transactions</span>
                </a>
                <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request('low_stock') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.7 3.86a2 2 0 00-3.4 0z"></path></svg>
                    <span>Low Stock</span>
                </a>
            </div>
        </div>
        @endif
        
        @if(Auth::user()->isAdmin())
        <div>
            <p class="px-3 text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">System</p>
            <div class="space-y-1">
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-primary-500 text-white shadow-sm' : 'hover:bg-slate-700 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Users</span>
                </a>
            </div>
        </div>
        @endif

    </nav>
    
    <!-- User Info in Sidebar (Desktop) -->
    <div class="p-4 bg-[#111c2b] border-t border-[#334155] mt-auto">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center font-bold text-primary-200">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-300 truncate">{{ Auth::user()->role->name }}</p>
            </div>
        </div>
    </div>
</aside>
