@extends('layouts.app')

@section('title', 'Add Room Type')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-stone-500 mb-2">
            <a href="{{ route('room-types.index') }}" class="hover:text-primary-700 transition-colors">Room Types</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-stone-700">Add Room Type</span>
        </div>
        <h1 class="text-2xl font-semibold text-stone-900">Add New Room Type</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <form method="POST" action="{{ route('room-types.store') }}" class="p-6 sm:p-8">
            @csrf
            @include('room_types._form')

            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-stone-200">
                <a href="{{ route('room-types.index') }}" class="px-4 py-2.5 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg hover:bg-stone-50 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-primary-700 rounded-lg hover:bg-primary-600 transition-colors">Save Room Type</button>
            </div>
        </form>
    </div>
@endsection
