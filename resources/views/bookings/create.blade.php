@extends('layouts.app')

@section('title', 'New Booking')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-stone-500 mb-2">
            <a href="{{ route('bookings.index') }}" class="hover:text-primary-700 transition-colors">Bookings</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-stone-700">New Booking</span>
        </div>
        <h1 class="text-2xl font-semibold text-stone-900">Create New Booking</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden" x-data="bookingForm()">
        <form method="POST" action="{{ route('bookings.store') }}" class="p-6 sm:p-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                {{-- Guest --}}
                <div class="md:col-span-2">
                    <label for="guest_id" class="block text-sm font-medium text-stone-700 mb-1.5">Guest <span class="text-red-500">*</span></label>
                    <select name="guest_id" id="guest_id" required
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">— Select Guest —</option>
                        @foreach($guests as $guest)
                            <option value="{{ $guest->id }}" @selected(old('guest_id') == $guest->id)>
                                {{ $guest->full_name }} ({{ $guest->identity_number }})
                            </option>
                        @endforeach
                    </select>
                    @error('guest_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Room --}}
                <div class="md:col-span-2">
                    <label for="room_id" class="block text-sm font-medium text-stone-700 mb-1.5">Room <span class="text-red-500">*</span></label>
                    <select name="room_id" id="room_id" required x-ref="roomSelect" x-on:change="updateRoomDetails"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">— Select Room —</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" 
                                data-price="{{ (int)$room->price_per_night }}"
                                data-capacity="{{ $room->capacity }}"
                                @selected(old('room_id') == $room->id)>
                                Room {{ $room->room_number }} - {{ $room->roomType->name }} (Rp {{ number_format($room->price_per_night, 0, ',', '.') }}/night)
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Check-in Date --}}
                <div>
                    <label for="check_in_date" class="block text-sm font-medium text-stone-700 mb-1.5">Check-in Date <span class="text-red-500">*</span></label>
                    <input type="date" name="check_in_date" id="check_in_date" x-model="checkInDate" required
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('check_in_date')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Check-out Date --}}
                <div>
                    <label for="check_out_date" class="block text-sm font-medium text-stone-700 mb-1.5">Check-out Date <span class="text-red-500">*</span></label>
                    <input type="date" name="check_out_date" id="check_out_date" x-model="checkOutDate" required
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('check_out_date')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Number of Guests --}}
                <div>
                    <label for="num_guests" class="block text-sm font-medium text-stone-700 mb-1.5">Number of Guests <span class="text-red-500">*</span></label>
                    <input type="number" name="num_guests" id="num_guests" x-model="numGuests" required min="1"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="mt-1 text-xs text-stone-500" x-show="maxCapacity">Max capacity: <span x-text="maxCapacity"></span></p>
                    @error('num_guests')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="hidden md:block"></div> {{-- Spacer --}}

                <div class="md:col-span-2 pt-4 border-t border-stone-200">
                    <h3 class="text-lg font-medium text-stone-900 mb-4">Pricing Adjustments</h3>
                </div>

                {{-- Discount --}}
                <div>
                    <label for="discount" class="block text-sm font-medium text-stone-700 mb-1.5">Discount (Rp)</label>
                    <input type="number" name="discount" id="discount" x-model="discount" min="0" step="1"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('discount')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tax --}}
                <div>
                    <label for="tax" class="block text-sm font-medium text-stone-700 mb-1.5">Tax (Rp)</label>
                    <input type="number" name="tax" id="tax" x-model="tax" min="0" step="1"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('tax')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Additional Charge --}}
                <div>
                    <label for="additional_charge" class="block text-sm font-medium text-stone-700 mb-1.5">Additional Charge (Rp)</label>
                    <input type="number" name="additional_charge" id="additional_charge" x-model="additionalCharge" min="0" step="1"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('additional_charge')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notes --}}
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-stone-700 mb-1.5">Notes</label>
                    <textarea name="notes" id="notes" rows="2"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Summary --}}
                <div class="md:col-span-2 bg-stone-50 rounded-lg p-4 mt-4 border border-stone-200">
                    <h4 class="font-medium text-stone-900 mb-2">Estimated Summary</h4>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-stone-600">Room Rate / Night:</span>
                            <span class="font-medium">Rp <span x-text="formatMoney(roomRate)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-600">Number of Nights:</span>
                            <span class="font-medium" x-text="numNights"></span>
                        </div>
                        <div class="flex justify-between border-t border-stone-200 pt-1 mt-1">
                            <span class="text-stone-600">Subtotal:</span>
                            <span class="font-medium">Rp <span x-text="formatMoney(roomRate * numNights)"></span></span>
                        </div>
                        <div class="flex justify-between text-green-600" x-show="discount > 0">
                            <span>Discount:</span>
                            <span>- Rp <span x-text="formatMoney(discount)"></span></span>
                        </div>
                        <div class="flex justify-between text-stone-600" x-show="tax > 0">
                            <span>Tax:</span>
                            <span>+ Rp <span x-text="formatMoney(tax)"></span></span>
                        </div>
                        <div class="flex justify-between text-stone-600" x-show="additionalCharge > 0">
                            <span>Additional Charge:</span>
                            <span>+ Rp <span x-text="formatMoney(additionalCharge)"></span></span>
                        </div>
                        <div class="flex justify-between border-t border-stone-200 pt-2 mt-2 text-base font-bold text-stone-900">
                            <span>Estimated Grand Total:</span>
                            <span>Rp <span x-text="formatMoney(grandTotal)"></span></span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-stone-200">
                <a href="{{ route('bookings.index') }}" class="px-4 py-2.5 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg hover:bg-stone-50 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-primary-700 rounded-lg hover:bg-primary-600 transition-colors">Create Booking</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingForm', () => ({
                checkInDate: '{{ old('check_in_date') }}',
                checkOutDate: '{{ old('check_out_date') }}',
                numGuests: '{{ old('num_guests', 1) }}',
                discount: {{ old('discount', 0) }},
                tax: {{ old('tax', 0) }},
                additionalCharge: {{ old('additional_charge', 0) }},
                
                roomRate: 0,
                maxCapacity: null,

                init() {
                    this.updateRoomDetails();
                },

                updateRoomDetails() {
                    const select = this.$refs.roomSelect;
                    if(select.selectedIndex > 0) {
                        const option = select.options[select.selectedIndex];
                        this.roomRate = parseInt(option.getAttribute('data-price')) || 0;
                        this.maxCapacity = parseInt(option.getAttribute('data-capacity')) || null;
                    } else {
                        this.roomRate = 0;
                        this.maxCapacity = null;
                    }
                },

                get numNights() {
                    if (!this.checkInDate || !this.checkOutDate) return 0;
                    const date1 = new Date(this.checkInDate);
                    const date2 = new Date(this.checkOutDate);
                    const diffTime = date2 - date1;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    return diffDays > 0 ? diffDays : 0;
                },

                get grandTotal() {
                    const subtotal = this.roomRate * this.numNights;
                    return subtotal - (Number(this.discount) || 0) + (Number(this.tax) || 0) + (Number(this.additionalCharge) || 0);
                },

                formatMoney(amount) {
                    return Number(amount).toLocaleString('id-ID');
                }
            }))
        })
    </script>
@endsection
