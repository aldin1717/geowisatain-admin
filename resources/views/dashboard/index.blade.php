@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8 flex flex-col gap-5 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div class="min-w-0">
            <p class="inline-flex items-center gap-2 text-sm font-medium text-primary-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ now()->format('l, d F Y') }}
            </p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">Welcome back, {{ $user->name }}!</h1>
            <p class="mt-1 text-sm text-stone-500">Here is today's overview for {{ config('app.name', 'Geowisata Inn') }}.</p>
        </div>
        <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
            @if($canManageHotel)
                <a href="{{ route('bookings.create') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    New booking
                </a>
            @endif
            @if($canManageInventory)
                <a href="{{ route('inventory.transactions.create', ['type' => 'stock_in']) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 shadow-sm transition hover:bg-stone-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Receive stock
                </a>
            @endif
        </div>
    </div>

    <div class="mb-8 grid grid-cols-1 items-stretch gap-4 sm:grid-cols-2 {{ $canManageHotel && $canManageInventory ? 'xl:grid-cols-4' : ($canManageHotel ? 'xl:grid-cols-3' : 'xl:grid-cols-1') }}">
        @if($canManageHotel)
            <a href="{{ route('bookings.index', ['status' => 'confirmed', 'check_in_date' => today()->toDateString()]) }}" class="group flex min-h-36 flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-stone-500">Arrivals today</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-stone-900">{{ $todayArrivals }}</p>
                <p class="mt-1 text-xs leading-5 text-stone-500">Confirmed bookings scheduled for today</p>
            </a>
            <a href="{{ route('bookings.index', ['status' => 'checked_in', 'check_out_date' => today()->toDateString()]) }}" class="group flex min-h-36 flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-stone-500">Departures today</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 8l4 4m0 0l-4 4m4-4H3m13 4v1a3 3 0 01-3 3H8a3 3 0 01-3-3V7a3 3 0 013-3h8a3 3 0 013 3v1" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-stone-900">{{ $todayDepartures }}</p>
                <p class="mt-1 text-xs leading-5 text-stone-500">Checked-in bookings scheduled to leave today</p>
            </a>
            <a href="{{ route('rooms.index') }}" class="group flex min-h-36 flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-stone-500">Available rooms</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-stone-900">{{ $availableRooms }}<span class="ml-2 text-base font-medium text-stone-400">/ {{ $activeRoomCount }}</span></p>
                <p class="mt-1 text-xs leading-5 text-stone-500">Active rooms currently marked available</p>
            </a>
        @endif

        @if($canManageInventory)
            <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="group flex min-h-36 flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
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

    <div class="grid grid-cols-1 items-start gap-6 {{ $canManageHotel && $canManageInventory ? 'xl:grid-cols-2' : 'xl:grid-cols-1' }}">
        @if($canManageHotel)
            <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="font-semibold text-stone-900">Recent bookings</h2>
                        <p class="mt-1 text-xs text-stone-500">Latest reservations added to the system</p>
                    </div>
                    <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-700 transition hover:text-primary-800 focus:outline-none focus:underline">
                        All bookings
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <div class="divide-y divide-stone-100">
                    @forelse($recentBookings as $booking)
                        <a href="{{ route('bookings.show', $booking) }}" class="flex items-center justify-between gap-3 px-5 py-4 transition hover:bg-stone-50 focus:bg-stone-50 focus:outline-none sm:gap-4 sm:px-6">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-stone-900">{{ $booking->guest->full_name }}</p>
                                <p class="mt-1 truncate text-xs text-stone-500">{{ $booking->booking_number }} <span aria-hidden="true">·</span> {{ $booking->room->room_number }} <span aria-hidden="true">·</span> {{ $booking->check_in_date->format('d M Y') }}</p>
                            </div>
                            @php
                                $bookingStatusColors = [
                                    'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                    'confirmed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                    'checked_in' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                    'checked_out' => 'bg-stone-100 text-stone-600 ring-stone-500/20',
                                    'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20',
                                    'no_show' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                ];
                                $bookingStatusColor = $bookingStatusColors[$booking->booking_status->value] ?? 'bg-stone-100 text-stone-600 ring-stone-500/20';
                            @endphp
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $bookingStatusColor }}">{{ $booking->booking_status->label() }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-stone-500">No bookings have been recorded yet.</p>
                    @endforelse
                </div>
            </section>
        @endif

        @if($canManageInventory)
            <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="font-semibold text-stone-900">Stock needing attention</h2>
                        <p class="mt-1 text-xs text-stone-500">Inventory items at or below their minimum level</p>
                    </div>
                    <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-700 transition hover:text-primary-800 focus:outline-none focus:underline">
                        View inventory
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <div class="divide-y divide-stone-100">
                    @forelse($lowStockItems as $item)
                        <a href="{{ route('inventory.index', ['low_stock' => 1, 'search' => $item->sku]) }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-stone-50 focus:bg-stone-50 focus:outline-none sm:px-6">
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
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="font-semibold text-stone-900">Recent stock movements</h2>
                        <p class="mt-1 text-xs text-stone-500">Latest inventory transactions</p>
                    </div>
                    <a href="{{ route('inventory.transactions.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-700 transition hover:text-primary-800 focus:outline-none focus:underline">
                        All transactions
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
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
