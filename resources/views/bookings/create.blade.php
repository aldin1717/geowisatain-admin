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
                <div class="md:col-span-2 pt-2">
                    <h3 class="text-lg font-medium text-stone-900">Guest Information</h3>
                    <p class="mt-1 text-sm text-stone-500">Guest records are created or updated from this booking.</p>
                </div>

                <div>
                    <label for="guest_full_name" class="block text-sm font-medium text-stone-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="guest_full_name" id="guest_full_name" value="{{ old('guest_full_name') }}" required
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('guest_full_name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="guest_phone" class="block text-sm font-medium text-stone-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                    <input type="tel" name="guest_phone" id="guest_phone" value="{{ old('guest_phone') }}" required
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('guest_phone')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="guest_email" class="block text-sm font-medium text-stone-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="guest_email" id="guest_email" value="{{ old('guest_email') }}" required
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('guest_email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="guest_address" class="block text-sm font-medium text-stone-700 mb-1.5">Address <span class="text-stone-400">(optional)</span></label>
                    <textarea name="guest_address" id="guest_address" rows="2"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('guest_address') }}</textarea>
                    @error('guest_address')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Booking Type --}}
                <div>
                    <label for="booking_type" class="block text-sm font-medium text-stone-700 mb-1.5">Booking Type</label>
                    <select name="booking_type" id="booking_type" class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach(\App\Enums\BookingType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('booking_type', 'general') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm font-medium text-stone-700">
                        <input type="checkbox" name="is_day_use" value="1" @checked(old('is_day_use'))>
                        Day Use / Ballroom
                    </label>
                </div>

                {{-- Room / Ballroom Selection --}}
                <div class="md:col-span-2">
                    <label for="selection_type" class="block text-sm font-medium text-stone-700 mb-1.5">Pilih Kamar / Ballroom <span class="text-red-500">*</span></label>
                    <select name="selection_type" id="selection_type" x-model="selectionType" x-on:change="syncSelectionType()"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="room" @selected(old('selection_type', 'room') === 'room')>Hanya Kamar</option>
                        <option value="ballroom" @selected(old('selection_type') === 'ballroom')>Hanya Ballroom</option>
                        <option value="both" @selected(old('selection_type') === 'both')>Kamar + Ballroom</option>
                    </select>
                    @error('room_ids')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2" x-show="selectionType !== 'ballroom'">
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Pilih Kamar</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse($hotelRooms as $room)
                            <label class="flex items-start gap-2 rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-700">
                                <input type="checkbox" name="room_ids[]" value="{{ $room->id }}"
                                    data-price="{{ (int)$room->price_per_night }}"
                                    data-capacity="{{ $room->capacity }}"
                                    data-category="room"
                                    @checked(in_array((string) $room->id, (array) old('room_ids', [])))
                                    x-on:change="syncRoomSelection()">
                                <span>
                                    Room {{ $room->room_number }} - {{ $room->roomType->name }}
                                    <br>
                                    <small class="text-stone-500">Kapasitas {{ $room->capacity }} orang · Rp {{ number_format($room->price_per_night, 0, ',', '.') }}/malam</small>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-stone-500">Belum ada kamar aktif pada data master.</p>
                        @endforelse
                    </div>
                </div>

                <div class="md:col-span-2" x-show="selectionType !== 'room'">
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Pilih Ballroom</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse($ballrooms as $ballroom)
                            <label class="flex items-start gap-2 rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-700">
                                <input type="checkbox" name="room_ids[]" value="{{ $ballroom->id }}"
                                    data-category="ballroom"
                                    @checked(in_array((string) $ballroom->id, (array) old('room_ids', [])))>
                                <span>
                                    Ballroom {{ $ballroom->room_number }} - {{ $ballroom->roomType->name }}
                                    <br>
                                    <small class="text-stone-500">Kapasitas {{ $ballroom->capacity }} orang</small>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-stone-500">Belum ada Ballroom aktif pada data master. Tambahkan tipe kategori Ballroom dan nomor Ballroom terlebih dahulu.</p>
                        @endforelse
                    </div>
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
                
                {{-- Itemized Additional Charges --}}
                <div class="md:col-span-2">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-medium text-stone-700">Biaya Tambahan</h4>
                            <p class="mt-0.5 text-xs text-stone-500">Tambahkan item seperti bantal ekstra, laundry, atau layanan lainnya.</p>
                        </div>
                        <button type="button" x-on:click="addAdditionalCharge()" class="rounded-lg border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50">
                            + Tambah Item
                        </button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(item, index) in additionalCharges" :key="index">
                            <div class="grid grid-cols-1 gap-3 rounded-lg border border-stone-200 bg-stone-50 p-3 sm:grid-cols-[minmax(0,1fr)_220px_auto]">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-stone-600">Nama item</label>
                                    <input type="text" x-model="item.name" x-bind:name="`additional_charge_breakdown[${index}][name]`" maxlength="100" placeholder="Contoh: Bantal tambahan"
                                        class="block w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-stone-600">Nominal (Rp)</label>
                                    <input type="number" x-model.number="item.amount" x-bind:name="`additional_charge_breakdown[${index}][amount]`" min="0.01" step="1" placeholder="0"
                                        class="block w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" x-on:click="removeAdditionalCharge(index)" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50 sm:w-auto" aria-label="Hapus item biaya tambahan">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    @error('additional_charge_breakdown')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @foreach($errors->get('additional_charge_breakdown.*.name') as $messages)
                        @foreach($messages as $message)
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    @endforeach
                    @foreach($errors->get('additional_charge_breakdown.*.amount') as $messages)
                        @foreach($messages as $message)
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    @endforeach
                </div>

                <div>
                    <label for="ballroom_amount" class="block text-sm font-medium text-stone-700 mb-1.5">Ballroom / Day Use (Rp)</label>
                    <input type="number" name="ballroom_amount" id="ballroom_amount" min="0" step="1" value="{{ old('ballroom_amount', 0) }}"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm font-medium text-stone-700">
                        <input type="checkbox" name="is_early_check_out" value="1" @checked(old('is_early_check_out'))>
                        Early check-out
                    </label>
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
                        <template x-for="(item, index) in additionalCharges.filter(item => item.name && Number(item.amount) > 0)" :key="`charge-summary-${index}`">
                            <div class="flex justify-between text-stone-600">
                                <span x-text="item.name"></span>
                                <span>+ Rp <span x-text="formatMoney(item.amount)"></span></span>
                            </div>
                        </template>
                        <div class="flex justify-between text-stone-600" x-show="additionalCharge > 0">
                            <span>Total Biaya Tambahan:</span>
                            <span>+ Rp <span x-text="formatMoney(additionalCharge)"></span></span>
                        </div>
                        <div class="flex justify-between text-stone-600" x-show="Number(document.getElementById('ballroom_amount')?.value || 0) > 0">
                            <span>Ballroom / Day Use:</span>
                            <span>+ Rp <span x-text="formatMoney(Number(document.getElementById('ballroom_amount')?.value || 0))"></span></span>
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
                selectionType: '{{ old('selection_type', 'room') }}',
                checkInDate: '{{ old('check_in_date') }}',
                checkOutDate: '{{ old('check_out_date') }}',
                numGuests: '{{ old('num_guests', 1) }}',
                discount: {{ old('discount', 0) }},
                tax: {{ old('tax', 0) }},
                additionalCharges: @js(old('additional_charge_breakdown', [['name' => '', 'amount' => '']])),
                roomRate: 0,
                maxCapacity: null,

                init() {
                    this.syncRoomSelection();
                },

                syncRoomSelection() {
                    const checkedRooms = Array.from(document.querySelectorAll('input[name="room_ids[]"]:checked'));
                    let totalPrice = 0;
                    let totalCapacity = 0;

                    checkedRooms.forEach((room) => {
                        if (room.dataset.category === 'room') {
                            totalPrice += Number(room.dataset.price || 0);
                            totalCapacity += Number(room.dataset.capacity || 0);
                        }
                    });

                    this.roomRate = totalPrice;
                    this.maxCapacity = totalCapacity > 0 ? totalCapacity : null;
                },

                syncSelectionType() {
                    document.querySelectorAll('input[name="room_ids[]"]').forEach((input) => {
                        const shouldKeep = this.selectionType === 'both'
                            || (this.selectionType === 'room' && input.dataset.category === 'room')
                            || (this.selectionType === 'ballroom' && input.dataset.category === 'ballroom');

                        if (!shouldKeep) {
                            input.checked = false;
                        }
                    });

                    this.syncRoomSelection();
                },

                addAdditionalCharge() {
                    this.additionalCharges.push({ name: '', amount: '' });
                },

                removeAdditionalCharge(index) {
                    this.additionalCharges.splice(index, 1);
                },

                get additionalCharge() {
                    return this.additionalCharges.reduce((total, item) => total + (Number(item.amount) || 0), 0);
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
                    const ballroomAmount = Number(document.getElementById('ballroom_amount')?.value || 0);
                    return subtotal - (Number(this.discount) || 0) + (Number(this.tax) || 0) + (Number(this.additionalCharge) || 0) + ballroomAmount;
                },

                formatMoney(amount) {
                    return Number(amount).toLocaleString('id-ID');
                }
            }))
        })
    </script>
@endsection
