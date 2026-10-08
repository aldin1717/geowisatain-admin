@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-primary-700">{{ now()->format('l, d F Y') }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Welcome back, {{ explode(' ', $user->name)[0] }}!</h1>
            <p class="mt-1 text-sm text-stone-500">Here is today's overview for {{ config('app.name', 'Geowisata Inn') }}.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if($canManageHotel)
                <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    New booking
                </a>
            @endif
            @if($canManageInventory)
                <a href="{{ route('inventory.transactions.create', ['type' => 'stock_in']) }}" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 shadow-sm transition hover:bg-stone-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Receive stock
                </a>
            @endif
        </div>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @if($canManageHotel)
            <a href="{{ route('bookings.index', ['status' => 'confirmed', 'check_in_date' => today()->toDateString()]) }}" class="group rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-stone-500">Arrivals today</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-stone-900">{{ $todayArrivals }}</p>
                <p class="mt-1 text-xs text-stone-500">Confirmed bookings scheduled for today</p>
            </a>
            <a href="{{ route('bookings.index', ['status' => 'checked_in', 'check_out_date' => today()->toDateString()]) }}" class="group rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-stone-500">Departures today</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 8l4 4m0 0l-4 4m4-4H3m13 4v1a3 3 0 01-3 3H8a3 3 0 01-3-3V7a3 3 0 013-3h8a3 3 0 013 3v1" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-stone-900">{{ $todayDepartures }}</p>
                <p class="mt-1 text-xs text-stone-500">Checked-in bookings scheduled to leave today</p>
            </a>
            <a href="{{ route('rooms.index') }}" class="group rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-stone-500">Available rooms</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-stone-900">{{ $availableRooms }}<span class="ml-2 text-base font-medium text-stone-400">/ {{ $activeRoomCount }}</span></p>
                <p class="mt-1 text-xs text-stone-500">Active rooms currently marked available</p>
            </a>
        @endif

        @if($canManageInventory)
            <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="group rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-stone-500">Low stock items</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-stone-900">{{ $lowStockCount }}</p>
                <p class="mt-1 text-xs text-stone-500">Items at or below their minimum stock level</p>
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        @if($canManageHotel)
            <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-stone-900">Recent bookings</h2>
                        <p class="mt-1 text-xs text-stone-500">Latest reservations added to the system</p>
                    </div>
                    <a href="{{ route('bookings.index') }}" class="text-sm font-semibold text-primary-700 hover:text-primary-800">All bookings</a>
                </div>
                <div class="divide-y divide-stone-100">
                    @forelse($recentBookings as $booking)
                        <a href="{{ route('bookings.show', $booking) }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-stone-50">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-stone-900">{{ $booking->guest->full_name }} <span class="font-normal text-stone-500">· {{ $booking->booking_number }}</span></p>
                                <p class="mt-1 text-xs text-stone-500">Room {{ $booking->room->room_number }} · {{ $booking->check_in_date->format('d M Y') }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-700">{{ $booking->booking_status->label() }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-stone-500">No bookings have been recorded yet.</p>
                    @endforelse
                </div>
            </section>
        @endif

        @if($canManageInventory)
            <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-stone-900">Stock needing attention</h2>
                        <p class="mt-1 text-xs text-stone-500">Inventory items at or below their minimum level</p>
                    </div>
                    <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="text-sm font-semibold text-primary-700 hover:text-primary-800">View inventory</a>
                </div>
                <div class="divide-y divide-stone-100">
                    @forelse($lowStockItems as $item)
                        <a href="{{ route('inventory.index', ['low_stock' => 1, 'search' => $item->sku]) }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-stone-50">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-stone-900">{{ $item->name }}</p>
                                <p class="mt-1 text-xs text-stone-500">{{ $item->sku }} · minimum {{ number_format((float) $item->minimum_stock, 2) }} {{ $item->unit }}</p>
                            </div>
                            <span class="shrink-0 text-sm font-semibold text-red-700">{{ number_format((float) $item->current_stock, 2) }} {{ $item->unit }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-emerald-700">All items are above their minimum stock levels.</p>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-stone-900">Recent stock movements</h2>
                        <p class="mt-1 text-xs text-stone-500">Latest inventory transactions</p>
                    </div>
                    <a href="{{ route('inventory.transactions.index') }}" class="text-sm font-semibold text-primary-700 hover:text-primary-800">All transactions</a>
                </div>
                <div class="divide-y divide-stone-100">
                    @forelse($recentTransactions as $transaction)
                        <div class="flex items-center justify-between gap-4 px-5 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-stone-900">{{ $transaction->item->name }}</p>
                                <p class="mt-1 text-xs text-stone-500">{{ $transaction->type->label() }} · {{ $transaction->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <span class="shrink-0 text-sm font-semibold {{ $transaction->type->value === 'stock_out' || (float) $transaction->quantity < 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                {{ $transaction->type->value === 'stock_out' ? '−' : ((float) $transaction->quantity > 0 ? '+' : '') }}{{ number_format((float) $transaction->quantity, 2) }} {{ $transaction->item->unit }}
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-stone-500">No stock movements have been recorded yet.</p>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
@endsection
