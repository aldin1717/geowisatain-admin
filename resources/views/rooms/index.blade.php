@extends('layouts.app')

@section('title', 'Rooms')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Rooms</h1>
            <p class="mt-1 text-sm text-stone-500">Manage individual hotel rooms and their statuses.</p>
        </div>
        <a href="{{ route('rooms.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-700 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Room
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('rooms.index') }}" class="flex flex-wrap gap-3">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search room number..."
                    class="block w-full rounded-lg border border-stone-300 py-2 pl-10 pr-3 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <div class="w-48">
                <select name="room_type_id" class="block w-full rounded-lg border border-stone-300 py-2 pl-3 pr-10 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Room Types</option>
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}" @selected(request('room_type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-stone-100 text-stone-700 text-sm font-medium rounded-lg hover:bg-stone-200 border border-stone-300 transition-colors">Filter</button>
            
            @if(request('search') || request('room_type_id'))
                <a href="{{ route('rooms.index') }}" class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700 transition-colors flex items-center">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Room #</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Floor</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Capacity</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Price/Night</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($rooms as $room)
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-stone-900">
                                <a href="{{ route('rooms.show', $room) }}" class="hover:text-primary-700">{{ $room->room_number }}</a>
                            </td>
                            <td class="px-6 py-4 text-stone-600">{{ $room->roomType->name }}</td>
                            <td class="px-6 py-4 text-stone-600">{{ $room->floor ?? '—' }}</td>
                            <td class="px-6 py-4 text-stone-600">{{ $room->capacity }}</td>
                            <td class="px-6 py-4 text-stone-600">Rp {{ number_format($room->price_per_night, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $color }}">
                                    {{ $room->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('rooms.show', $room) }}" class="text-stone-400 hover:text-primary-600 transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="{{ route('rooms.edit', $room) }}" class="text-stone-400 hover:text-amber-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-stone-500">
                                <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <p class="font-medium">No rooms found</p>
                                <p class="text-sm mt-1">Get started by adding a new room.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rooms->hasPages())
            <div class="px-6 py-4 border-t border-stone-200 bg-stone-50">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>
@endsection
