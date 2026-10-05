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
                <a href="{{ route('room-types.edit', $roomType) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg hover:bg-stone-50 transition-colors">Edit</a>
                <form method="POST" action="{{ route('room-types.destroy', $roomType) }}" onsubmit="return confirm('Delete this room type?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-900">Category</h2>
            <p class="mt-3 text-sm text-stone-700">{{ $roomType->category === 'ballroom' ? 'Ballroom' : 'Room' }}</p>
            <h2 class="font-semibold text-stone-900">Description</h2>
            <p class="mt-3 text-sm text-stone-700">{{ $roomType->description ?: 'No description provided.' }}</p>
        </div>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between">
                <h2 class="font-semibold text-stone-900">Associated Rooms</h2>
                <span class="text-sm text-stone-500">{{ $roomType->rooms->count() }} room(s)</span>
            </div>
            @if($roomType->rooms->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-stone-50 border-b border-stone-200">
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Room Number</th>
                                <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Floor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($roomType->rooms as $room)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-stone-900">{{ $room->room_number }}</td>
                                    <td class="px-6 py-4 text-stone-600">{{ $room->floor ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center text-stone-500">There are no rooms assigned to this type yet.</div>
            @endif
        </div>
    </div>
@endsection
