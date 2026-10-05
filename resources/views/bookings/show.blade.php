@extends('layouts.app')

@section('title', 'Booking ' . $booking->booking_number)

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-stone-500 mb-2">
            <a href="{{ route('bookings.index') }}" class="hover:text-primary-700 transition-colors">Bookings</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-stone-700">{{ $booking->booking_number }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-2xl font-semibold text-stone-900">Booking {{ $booking->booking_number }}</h1>
            <div class="flex items-center gap-3">
                
                {{-- Actions based on Status --}}
                @if($booking->booking_status->value === 'confirmed')
                    <form method="POST" action="{{ route('bookings.check-in', $booking) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            Process Check-in
                        </button>
                    </form>
                @endif

                @if($booking->booking_status->value === 'checked_in')
                    <form method="POST" action="{{ route('bookings.check-out', $booking) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Process Check-out
                        </button>
                    </form>
                @endif

                <a href="{{ route('bookings.edit', $booking) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg hover:bg-stone-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Status
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Booking Summary --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200">
                    <h3 class="font-semibold text-stone-900">Reservation Details</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Status</p>
                            <p class="mt-1">
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
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-medium {{ $color }}">
                                    {{ $booking->booking_status->label() }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Check-in Date</p>
                            <p class="mt-1 font-medium text-stone-900">{{ $booking->check_in_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Check-out Date</p>
                            <p class="mt-1 font-medium text-stone-900">{{ $booking->check_out_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Nights</p>
                            <p class="mt-1 text-stone-900">{{ $booking->num_nights }} night(s)</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Guests</p>
                            <p class="mt-1 text-stone-900">{{ $booking->num_guests }} person(s)</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Booking Type</p>
                            <p class="mt-1 text-stone-900">{{ $booking->booking_type ? $booking->booking_type->label() : 'Umum' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Created By</p>
                            <p class="mt-1 text-stone-900">{{ $booking->creator->name ?? 'System' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-stone-100 grid grid-cols-2 gap-3 text-sm">
                        @if($booking->is_day_use)
                            <div class="rounded-lg bg-blue-50 border border-blue-200 px-3 py-2 text-blue-700">Day Use / Ballroom</div>
                        @endif
                        @if($booking->is_early_check_out)
                            <div class="rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-amber-700">Early Check-out</div>
                        @endif
                        @if($booking->is_bill_merged)
                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-emerald-700">Merged Bill</div>
                        @endif
                    </div>

                    @if($booking->actual_check_in || $booking->actual_check_out)
                    <div class="mt-6 pt-6 border-t border-stone-100 grid grid-cols-2 gap-6">
                        @if($booking->actual_check_in)
                        <div>
                            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Actual Check-in</p>
                            <p class="mt-1 text-sm text-stone-900">{{ $booking->actual_check_in->format('d M Y, H:i') }}</p>
                            <p class="text-xs text-stone-500">by {{ $booking->checkedInBy->name ?? 'Unknown' }}</p>
                        </div>
                        @endif
                        @if($booking->actual_check_out)
                        <div>
                            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider">Actual Check-out</p>
                            <p class="mt-1 text-sm text-stone-900">{{ $booking->actual_check_out->format('d M Y, H:i') }}</p>
                            <p class="text-xs text-stone-500">by {{ $booking->checkedOutBy->name ?? 'Unknown' }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($booking->notes)
                    <div class="mt-6 pt-6 border-t border-stone-100">
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-2">Notes</p>
                        <p class="text-sm text-stone-700 bg-stone-50 p-3 rounded-lg">{{ $booking->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Guest & Room info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden p-6">
                    <h3 class="font-semibold text-stone-900 mb-4">Guest Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-stone-500">Name</p>
                            <p class="font-medium text-stone-900">
                                <a href="{{ route('guests.show', $booking->guest) }}" class="text-primary-700 hover:underline">{{ $booking->guest->full_name }}</a>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-stone-500">Identity</p>
                            <p class="text-sm text-stone-900">
                            @if($booking->guest->identity_type)
                                {{ $booking->guest->identity_type->label() }}
                            @endif
                            @if($booking->guest->identity_number)
                                @if($booking->guest->identity_type)
                                    -
                                @endif
                                {{ $booking->guest->identity_number }}
                            @elseif(! $booking->guest->identity_type)
                                —
                            @endif
                        </p>
                        </div>
                        <div>
                            <p class="text-xs text-stone-500">Contact</p>
                            <p class="text-sm text-stone-900">{{ $booking->guest->phone ?? '—' }}</p>
                            <p class="text-sm text-stone-900">{{ $booking->guest->email ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden p-6">
                    <h3 class="font-semibold text-stone-900 mb-4">Kamar / Ballroom yang Dipilih</h3>
                    <div class="space-y-4">
                        @foreach($selectedRooms as $room)
                            <div class="border-b border-stone-100 pb-3 last:border-0 last:pb-0">
                                <p class="font-medium text-stone-900">
                                    <a href="{{ route('rooms.show', $room) }}" class="text-primary-700 hover:underline">
                                        {{ $room->roomType->category === 'ballroom' ? 'Ballroom' : 'Kamar' }} {{ $room->room_number }}
                                    </a>
                                </p>
                                <p class="text-sm text-stone-600">{{ $room->roomType->name }}</p>
                                <p class="text-xs text-stone-500">Status: {{ $room->status->label() }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Summary --}}
        <div class="lg:col-span-1 space-y-6">
            @php
                $totalPaid = $booking->totalPaid();
                $outstanding = max((float) $booking->grand_total - $totalPaid, 0);
            @endphp

            @if($booking->billingGroup)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                    Booking ini tergabung dalam tagihan <a class="font-semibold underline" href="{{ route('billing-groups.show', $booking->billingGroup) }}">{{ $booking->billingGroup->invoice_number }}</a>.
                    Pembayaran dicatat satu kali dari halaman tagihan gabungan oleh {{ $booking->billingGroup->payerGuest->full_name }}.
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200">
                    <h3 class="font-semibold text-stone-900">Financial Summary</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-stone-500">Room Rate / Night</span>
                            <span class="text-stone-900">Rp {{ number_format($booking->room_rate, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-500">Number of Nights</span>
                            <span class="text-stone-900">{{ $booking->num_nights }}</span>
                        </div>
                        <div class="flex justify-between border-t border-stone-100 pt-2 mt-2">
                            <span class="text-stone-500">Subtotal</span>
                            <span class="font-medium text-stone-900">Rp {{ number_format($booking->room_rate * $booking->num_nights, 0, ',', '.') }}</span>
                        </div>
                        
                        @if($booking->discount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Discount</span>
                            <span>- Rp {{ number_format($booking->discount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($booking->tax > 0)
                        <div class="flex justify-between text-stone-600">
                            <span>Tax</span>
                            <span>+ Rp {{ number_format($booking->tax, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if(! empty($booking->additional_charge_breakdown))
                            @foreach($booking->additional_charge_breakdown as $charge)
                                <div class="flex justify-between text-stone-600">
                                    <span>{{ $charge['name'] }}</span>
                                    <span>+ Rp {{ number_format($charge['amount'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        @elseif($booking->additional_charge > 0)
                            <div class="flex justify-between text-stone-600">
                                <span>Additional Charges</span>
                                <span>+ Rp {{ number_format($booking->additional_charge, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if(! empty($booking->additional_charge_breakdown) && $booking->additional_charge > 0)
                            <div class="flex justify-between border-t border-stone-100 pt-2 font-medium text-stone-700">
                                <span>Total Biaya Tambahan</span>
                                <span>Rp {{ number_format($booking->additional_charge, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($booking->ballroom_amount > 0)
                        <div class="flex justify-between text-stone-600">
                            <span>Ballroom / Day Use</span>
                            <span>+ Rp {{ number_format($booking->ballroom_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between border-t border-stone-200 pt-3 mt-3 text-lg font-bold text-stone-900">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between pt-2">
                            <span class="text-stone-500">Total Paid</span>
                            <span class="font-medium text-emerald-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between pt-2">
                            <span class="text-stone-500">Outstanding</span>
                            <span class="font-medium {{ $outstanding > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                Rp {{ number_format($outstanding, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200">
                    <h3 class="font-semibold text-stone-900">Payment</h3>
                </div>
                <div class="p-6">
                    @if($booking->billingGroup)
                        <a href="{{ route('billing-groups.show', $booking->billingGroup) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-600">
                            Buka Tagihan Gabungan
                        </a>
                    @elseif($outstanding > 0)
                        <form method="POST" action="{{ route('bookings.payments.store', $booking) }}" class="space-y-4">
                            @csrf

                            <div>
                                <label for="amount" class="block text-sm font-medium text-stone-700 mb-1">Amount</label>
                                <input id="amount" name="amount" type="number" step="0.01" min="0.01" max="{{ $outstanding }}" value="{{ old('amount', number_format($outstanding, 2, '.', '')) }}" required class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-stone-700 mb-1">Payment Method</label>
                                <select id="payment_method" name="payment_method" required class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-stone-700 mb-1">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Optional payment notes">{{ old('notes') }}</textarea>
                            </div>

                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-500 transition-colors">
                                Record Payment
                            </button>
                        </form>
                    @else
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            This booking has been fully paid.
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200">
                    <h3 class="font-semibold text-stone-900">Payment History</h3>
                </div>
                <div class="p-0">
                    @if($booking->payments->isNotEmpty())
                        <div class="divide-y divide-stone-200">
                            @foreach($booking->payments as $payment)
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-stone-900">{{ $payment->payment_number }}</p>
                                            <p class="text-xs text-stone-500">{{ $payment->payment_date->format('d M Y, H:i') }} • {{ $payment->payment_method->label() }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-stone-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                                            <p class="text-xs {{ $payment->payment_status->value === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $payment->payment_status->label() }}</p>
                                        </div>
                                    </div>
                                    @if($payment->notes)
                                        <p class="mt-2 text-xs text-stone-500">{{ $payment->notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-4 text-sm text-stone-500">No payment recorded yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
