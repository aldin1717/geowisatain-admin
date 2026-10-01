@extends('layouts.app')

@section('title', 'Guests')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Guests</h1>
            <p class="mt-1 text-sm text-stone-500">Manage hotel guests and their information.</p>
        </div>
        <a href="{{ route('guests.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-700 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Guest
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('guests.index') }}" class="flex gap-3">
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, ID, phone, or email..."
                    class="block w-full rounded-lg border border-stone-300 py-2 pl-10 pr-3 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-stone-100 text-stone-700 text-sm font-medium rounded-lg hover:bg-stone-200 border border-stone-300 transition-colors">Search</button>
            @if(request('search'))
                <a href="{{ route('guests.index') }}" class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700 transition-colors flex items-center">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Guest Code</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Identity</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">Bookings</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($guests as $guest)
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-stone-600">{{ $guest->guest_code }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('guests.show', $guest) }}" class="font-medium text-stone-900 hover:text-primary-700">{{ $guest->full_name }}</a>
                            </td>
                            <td class="px-6 py-4 text-stone-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-stone-100 text-stone-700">{{ $guest->identity_type->label() }}</span>
                                <span class="ml-1 text-xs">{{ $guest->identity_number }}</span>
                            </td>
                            <td class="px-6 py-4 text-stone-600">{{ $guest->phone ?? '—' }}</td>
                            <td class="px-6 py-4 text-stone-600">{{ $guest->email ?? '—' }}</td>
                            <td class="px-6 py-4 text-stone-600">{{ $guest->bookings_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('guests.show', $guest) }}" class="text-stone-400 hover:text-primary-600 transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="{{ route('guests.edit', $guest) }}" class="text-stone-400 hover:text-amber-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-stone-500">
                                <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <p class="font-medium">No guests found</p>
                                <p class="text-sm mt-1">Get started by adding a new guest.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($guests->hasPages())
            <div class="px-6 py-4 border-t border-stone-200 bg-stone-50">
                {{ $guests->links() }}
            </div>
        @endif
    </div>
@endsection
