@extends('layouts.app')

@section('title', $guest->full_name)

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-stone-500 mb-2">
            <a href="{{ route('guests.index') }}" class="hover:text-primary-700 transition-colors">Guests</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-stone-700">{{ $guest->full_name }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-2xl font-semibold text-stone-900">{{ $guest->full_name }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('guests.edit', $guest) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg hover:bg-stone-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('guests.destroy', $guest) }}" x-data x-ref="deleteForm"
                    onsubmit="return confirm('Are you sure you want to delete this guest? This action cannot be undone.')">
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
        {{-- Guest Information --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200">
                    <h3 class="font-semibold text-stone-900">Guest Information</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Guest Code</p>
                        <p class="mt-1 text-sm font-mono text-stone-900">{{ $guest->guest_code }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Full Name</p>
                        <p class="mt-1 text-sm text-stone-900">{{ $guest->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Gender</p>
                        <p class="mt-1 text-sm text-stone-900">{{ $guest->gender ? ucfirst($guest->gender) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Identity</p>
                        <p class="mt-1 text-sm text-stone-900">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-stone-100 text-stone-700">{{ $guest->identity_type->label() }}</span>
                            {{ $guest->identity_number }}
                        </p>
                    </div>

                    @if($guest->phone)
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Phone</p>
                        <p class="mt-1 text-sm text-stone-900">{{ $guest->phone }}</p>
                    </div>
                    @endif

                    @if($guest->email)
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Email</p>
                        <p class="mt-1 text-sm text-stone-900">{{ $guest->email }}</p>
                    </div>
                    @endif

                    @if($guest->address)
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Address</p>
                        <p class="mt-1 text-sm text-stone-900">{{ $guest->address }}</p>
                    </div>
                    @endif

                    @if($guest->city || $guest->country)
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">City / Country</p>
                        <p class="mt-1 text-sm text-stone-900">{{ collect([$guest->city, $guest->country])->filter()->join(', ') ?: '—' }}</p>
                    </div>
                    @endif

                    @if($guest->notes)
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Notes</p>
                        <p class="mt-1 text-sm text-stone-700">{{ $guest->notes }}</p>
                    </div>
                    @endif

                    <div class="pt-3 border-t border-stone-100">
                        <p class="text-xs text-stone-400">Registered {{ $guest->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Booking History --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between">
                    <h3 class="font-semibold text-stone-900">Booking History</h3>
                    <span class="text-sm text-stone-500">{{ $guest->bookings->count() }} booking(s)</span>
                </div>

                @if($guest->bookings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-stone-50 border-b border-stone-200">
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Booking #</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Check-in</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Check-out</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($guest->bookings as $booking)
                            <tr class="hover:bg-stone-50">
                                <td class="px-6 py-3 font-mono text-xs text-stone-600">{{ $booking->booking_number }}</td>
                                <td class="px-6 py-3 text-stone-900">
                                    {{ $booking->room->room_number ?? '—' }}
                                    @if($booking->room?->roomType)
                                        <span class="text-xs text-stone-500">({{ $booking->room->roomType->name }})</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-stone-600">{{ $booking->check_in_date->format('d M Y') }}</td>
                                <td class="px-6 py-3 text-stone-600">{{ $booking->check_out_date->format('d M Y') }}</td>
                                <td class="px-6 py-3">
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
                                <td class="px-6 py-3 text-right text-stone-900 font-medium">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-12 text-center text-stone-500">
                    <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <p class="font-medium">No bookings yet</p>
                    <p class="text-sm mt-1">This guest has no booking history.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
