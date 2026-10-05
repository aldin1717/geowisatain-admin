@extends('layouts.app')

@section('title', 'Bookings')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Bookings</h1>
            <p class="mt-1 text-sm text-stone-500">Manage hotel reservations and check-in/out.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('billing-groups.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-stone-300 text-stone-700 text-sm font-medium rounded-lg hover:bg-stone-50 transition-colors">
                Buat Tagihan Gabungan
            </a>
            @if(!in_array(request('status'), ['confirmed', 'checked_in'], true))
                <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-700 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Booking
                </a>
            @endif
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('bookings.index') }}" class="flex flex-wrap gap-3">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search booking # or guest name..."
                    class="block w-full rounded-lg border border-stone-300 py-2 pl-10 pr-3 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <div class="w-48">
                <select name="status" class="block w-full rounded-lg border border-stone-300 py-2 pl-3 pr-10 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Statuses</option>
                    @foreach(\App\Enums\BookingStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-stone-100 text-stone-700 text-sm font-medium rounded-lg hover:bg-stone-200 border border-stone-300 transition-colors">Filter</button>
            
            @if(request('search') || request('status'))
                <a href="{{ route('bookings.index') }}" class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700 transition-colors flex items-center">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Booking #</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Room</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-stone-600">
                                <a href="{{ route('bookings.show', $booking) }}" class="font-medium text-primary-700 hover:underline">{{ $booking->booking_number }}</a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-stone-900">{{ $booking->guest->full_name }}</div>
                                <div class="text-xs text-stone-500">{{ $booking->guest->phone ?? $booking->guest->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @foreach($booking->rooms as $selectedRoom)
                                    <div class="font-medium text-stone-900">
                                        {{ $selectedRoom->roomType->category === 'ballroom' ? 'Ballroom' : 'Kamar' }} {{ $selectedRoom->room_number }}
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 text-stone-600">
                                {{ $booking->check_in_date->format('d M') }} - {{ $booking->check_out_date->format('d M Y') }}
                                <div class="text-xs text-stone-500">{{ $booking->num_nights }} night(s)</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                        'checked_in' => 'bg-emerald-100 text-emerald-800',
                                        'checked_out' => 'bg-stone-100 text-stone-700',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'no_show' => 'bg-orange-100 text-orange-800',
                                    ];
                                    $color = $statusColors[$booking->booking_status->value] ?? 'bg-stone-100 text-stone-700';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $color }}">
                                    {{ $booking->booking_status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-stone-900">
                                Rp {{ number_format($booking->grand_total, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-stone-500">
                                <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="font-medium">No bookings found</p>
                                @if(request('status') === 'confirmed')
                                    <p class="text-sm mt-1">Check-in bookings will appear here when available.</p>
                                @elseif(request('status') === 'checked_in')
                                    <p class="text-sm mt-1">Check-out bookings will appear here when available.</p>
                                @else
                                    <p class="text-sm mt-1">Get started by creating a new booking.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-stone-200 bg-stone-50">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
@endsection
