@extends('layouts.app')

@section('title', 'Room ' . $room->room_number)

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-stone-500 mb-2">
            <a href="{{ route('rooms.index') }}" class="hover:text-primary-700 transition-colors">Rooms</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-stone-700">Room {{ $room->room_number }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-2xl font-semibold text-stone-900">Room {{ $room->room_number }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('rooms.edit', $room) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg hover:bg-stone-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('rooms.destroy', $room) }}"
                    onsubmit="return confirm('Are you sure you want to delete this room? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Room Information --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                @if($room->image)
                    <img src="{{ Storage::url($room->image) }}" alt="Room {{ $room->room_number }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-stone-100 flex items-center justify-center text-stone-400">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                @endif
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Status</p>
                        <p class="mt-1">
                            @php
                                $statusColors = [
                                    'available' => 'bg-emerald-100 text-emerald-800',
                                    'reserved' => 'bg-blue-100 text-blue-800',
                                    'occupied' => 'bg-amber-100 text-amber-800',
                                    'dirty' => 'bg-orange-100 text-orange-800',
                                    'cleaning' => 'bg-cyan-100 text-cyan-800',
                                    'maintenance' => 'bg-purple-100 text-purple-800',
                                    'out_of_service' => 'bg-red-100 text-red-800',
                                ];
                                $color = $statusColors[$room->status->value] ?? 'bg-stone-100 text-stone-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-medium {{ $color }}">
                                {{ $room->status->label() }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Room Type</p>
                        <p class="mt-1 text-sm text-stone-900 font-medium">
                            <a href="{{ route('room-types.show', $room->roomType) }}" class="hover:text-primary-700">{{ $room->roomType->name }}</a>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Price / Night</p>
                        <p class="mt-1 text-lg font-semibold text-stone-900">Rp {{ number_format($room->price_per_night, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Capacity</p>
                        <p class="mt-1 text-sm text-stone-900">{{ $room->capacity }} {{ Str::plural('person', $room->capacity) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Floor</p>
                        <p class="mt-1 text-sm text-stone-900">{{ $room->floor ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Active Status</p>
                        <p class="mt-1 text-sm text-stone-900">{{ $room->is_active ? 'Yes' : 'No' }}</p>
                    </div>
                    @if($room->description)
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Specific Notes</p>
                        <p class="mt-1 text-sm text-stone-700">{{ $room->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bookings --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between">
                    <h3 class="font-semibold text-stone-900">Recent Bookings</h3>
                </div>

                @if($room->bookings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-stone-50 border-b border-stone-200">
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Booking #</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Guest</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Check In/Out</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($room->bookings()->latest()->take(5)->get() as $booking)
                            <tr class="hover:bg-stone-50">
                                <td class="px-6 py-3 font-mono text-xs text-stone-600">{{ $booking->booking_number }}</td>
                                <td class="px-6 py-3 text-stone-900 font-medium">{{ $booking->guest->full_name ?? '—' }}</td>
                                <td class="px-6 py-3 text-stone-600">
                                    {{ $booking->check_in_date->format('d M y') }} - {{ $booking->check_out_date->format('d M y') }}
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-stone-100 text-stone-700">
                                        {{ $booking->booking_status->label() }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-12 text-center text-stone-500">
                    <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <p class="font-medium">No bookings yet</p>
                    <p class="text-sm mt-1">This room hasn't been booked.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
