@extends('layouts.app')

@section('title', 'Edit Booking ' . $booking->booking_number)

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-stone-500 mb-2">
            <a href="{{ route('bookings.index') }}" class="hover:text-primary-700 transition-colors">Bookings</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <a href="{{ route('bookings.show', $booking) }}" class="hover:text-primary-700 transition-colors">{{ $booking->booking_number }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-stone-700">Edit Status</span>
        </div>
        <h1 class="text-2xl font-semibold text-stone-900">Edit Booking</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <form method="POST" action="{{ route('bookings.update', $booking) }}" class="p-6 sm:p-8">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6">
                {{-- Status --}}
                <div>
                    <label for="booking_status" class="block text-sm font-medium text-stone-700 mb-1.5">Booking Status <span class="text-red-500">*</span></label>
                    <select name="booking_status" id="booking_status" required
                        class="block w-full max-w-md rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach(\App\Enums\BookingStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('booking_status', $booking->booking_status->value) === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('booking_status')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-stone-700 mb-1.5">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="block w-full max-w-md rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes', $booking->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-start gap-3 mt-8 pt-6 border-t border-stone-200">
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-primary-700 rounded-lg hover:bg-primary-600 transition-colors">Update Booking</button>
                <a href="{{ route('bookings.show', $booking) }}" class="px-4 py-2.5 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg hover:bg-stone-50 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
@endsection
