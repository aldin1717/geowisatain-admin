@extends('layouts.app')

@section('title', $billingGroup->invoice_number)

@section('content')
    @php
        $totalAmount = $billingGroup->totalAmount();
        $paidAmount = $billingGroup->paidAmount();
        $outstandingAmount = $billingGroup->outstandingAmount();
    @endphp

    <div class="mb-6">
        <a href="{{ route('billing-groups.index') }}" class="text-sm text-stone-500 hover:text-primary-700">Tagihan Gabungan</a>
        <div class="mt-2 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">{{ $billingGroup->invoice_number }}</h1>
                <p class="mt-1 text-sm text-stone-500">Pembayar: {{ $billingGroup->payerGuest->full_name }}</p>
            </div>
            <span class="w-fit rounded-full bg-stone-100 px-3 py-1 text-sm font-medium capitalize text-stone-700">{{ $billingGroup->payment_status }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-200 px-6 py-4"><h2 class="font-semibold text-stone-900">Booking dalam Tagihan</h2></div>
                <div class="divide-y divide-stone-100">
                    @foreach($billingGroup->bookings as $booking)
                        <div class="flex flex-col justify-between gap-2 px-6 py-4 sm:flex-row">
                            <div>
                                <a href="{{ route('bookings.show', $booking) }}" class="font-medium text-primary-700 hover:underline">{{ $booking->booking_number }}</a>
                                <p class="text-sm text-stone-600">{{ $booking->guest->full_name }}</p>
                                <p class="text-xs text-stone-500">{{ $booking->check_in_date->format('d M Y') }} – {{ $booking->check_out_date->format('d M Y') }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="font-medium text-stone-900">Total Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</p>
                                <p class="text-sm text-stone-500">Sudah dibayar Rp {{ number_format($booking->totalPaid(), 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-200 px-6 py-4"><h2 class="font-semibold text-stone-900">Riwayat Pembayaran</h2></div>
                @forelse($billingGroup->payments as $payment)
                    <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-6 py-4 last:border-0">
                        <div>
                            <p class="font-medium text-stone-900">{{ $payment->payment_number }}</p>
                            <p class="text-xs text-stone-500">{{ $payment->payment_date->format('d M Y, H:i') }} · {{ $payment->payment_method->label() }}</p>
                            @if($payment->notes)<p class="mt-1 text-xs text-stone-500">{{ $payment->notes }}</p>@endif
                        </div>
                        <p class="font-semibold text-stone-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="px-6 py-5 text-sm text-stone-500">Belum ada pembayaran.</p>
                @endforelse
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold text-stone-900">Ringkasan Tagihan</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-stone-500">Total</dt><dd class="font-medium">Rp {{ number_format($totalAmount, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-stone-500">Sudah dibayar</dt><dd class="font-medium text-emerald-700">Rp {{ number_format($paidAmount, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between border-t border-stone-200 pt-3"><dt class="font-semibold text-stone-700">Sisa</dt><dd class="font-bold text-stone-900">Rp {{ number_format($outstandingAmount, 0, ',', '.') }}</dd></div>
                </dl>
            </section>

            @if($outstandingAmount > 0)
                <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold text-stone-900">Catat Pembayaran</h2>
                    <p class="mt-1 text-sm text-stone-500">Pembayaran ini akan dibagikan otomatis ke booking dalam tagihan.</p>
                    <form method="POST" action="{{ route('billing-groups.payments.store', $billingGroup) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label for="amount" class="mb-1 block text-sm font-medium text-stone-700">Nominal (Rp)</label>
                            <input id="amount" name="amount" type="number" min="0.01" max="{{ $outstandingAmount }}" step="0.01" value="{{ old('amount', number_format($outstandingAmount, 2, '.', '')) }}" required class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                            @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="payment_method" class="mb-1 block text-sm font-medium text-stone-700">Metode Pembayaran</label>
                            <select id="payment_method" name="payment_method" required class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                                @foreach(\App\Enums\PaymentMethod::cases() as $method)
                                    <option value="{{ $method->value }}">{{ $method->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="notes" class="mb-1 block text-sm font-medium text-stone-700">Catatan</label>
                            <textarea id="notes" name="notes" rows="2" class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-600">Catat Pembayaran Gabungan</button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
@endsection
