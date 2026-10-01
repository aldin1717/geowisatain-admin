@extends('layouts.app')

@section('title', $roomType->name)

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-stone-500 mb-2">
            <a href="{{ route('room-types.index') }}" class="hover:text-primary-700 transition-colors">Room Types</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-stone-700">{{ $roomType->name }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-2xl font-semibold text-stone-900">{{ $roomType->name }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('room-types.edit', $roomType) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg hover:bg-stone-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('room-types.destroy', $roomType) }}"
                    onsubmit="return confirm('Are you sure you want to delete this room type? This action cannot be undone.')">
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
        {{-- Info --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                @if($roomType->image)
                    <img src="{{ Storage::url($roomType->image) }}" alt="{{ $roomType->name }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-stone-100 flex items-center justify-center text-stone-400">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                @endif
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Base Price</p>
                        <p class="mt-1 text-lg font-semibold text-stone-900">Rp {{ number_format($roomType->base_price, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Capacity</p>
                        <p class="mt-1 text-sm text-stone-900">{{ $roomType->capacity }} {{ Str::plural('person', $roomType->capacity) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Status</p>
                        <p class="mt-1">
                            @if($roomType->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-stone-100 text-stone-800">Inactive</span>
                            @endif
                        </p>
                    </div>
                    @if($roomType->description)
                    <div>
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Description</p>
                        <p class="mt-1 text-sm text-stone-700">{{ $roomType->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Rooms List --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between">
                    <h3 class="font-semibold text-stone-900">Associated Rooms</h3>
                    <span class="text-sm text-stone-500">{{ $roomType->rooms->count() }} room(s)</span>
                </div>

                @if($roomType->rooms->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-stone-50 border-b border-stone-200">
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Room Number</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Floor</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider text-right">Specific Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($roomType->rooms as $room)
                            <tr class="hover:bg-stone-50">
                                <td class="px-6 py-4 font-medium text-stone-900">{{ $room->room_number }}</td>
                                <td class="px-6 py-4 text-stone-600">{{ $room->floor ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-stone-100 text-stone-700">
                                        {{ $room->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-stone-600">
                                    Rp {{ number_format($room->price_per_night, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-12 text-center text-stone-500">
                    <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <p class="font-medium">No rooms assigned</p>
                    <p class="text-sm mt-1">There are no rooms assigned to this type yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
