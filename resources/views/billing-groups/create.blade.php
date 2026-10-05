@extends('layouts.app')

@section('title', 'Buat Tagihan Gabungan')

@section('content')
    <div class="mb-6">
        <a href="{{ route('billing-groups.index') }}" class="text-sm text-stone-500 hover:text-primary-700">Tagihan Gabungan</a>
        <h1 class="mt-2 text-2xl font-semibold text-stone-900">Buat Tagihan Gabungan</h1>
        <p class="mt-1 text-sm text-stone-500">Pilih minimal dua booking belum lunas dan tentukan satu pelanggan sebagai pembayar.</p>
    </div>

    <form method="POST" action="{{ route('billing-groups.store') }}" class="space-y-6">
        @csrf
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-200 px-6 py-4">
                <h2 class="font-semibold text-stone-900">Pilih Booking</h2>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse($bookings as $booking)
                    <label class="flex cursor-pointer items-start gap-3 px-6 py-4 hover:bg-stone-50">
                        <input type="checkbox" name="booking_ids[]" value="{{ $booking->id }}"
                            @checked(in_array((string) $booking->id, (array) old('booking_ids', [])))
                            class="mt-1 rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                        <span class="flex-1">
                            <span class="flex flex-col justify-between gap-1 sm:flex-row">
                                <span class="font-medium text-stone-900">{{ $booking->booking_number }} — {{ $booking->guest->full_name }}</span>
                                <span class="font-medium text-stone-700">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</span>
                            </span>
                            <span class="mt-1 block text-sm text-stone-500">
                                {{ $booking->check_in_date->format('d M Y') }} – {{ $booking->check_out_date->format('d M Y') }}
                                · Status pembayaran: {{ $booking->payment_status->label() }}
                            </span>
                        </span>
                    </label>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-stone-500">Tidak ada booking aktif yang belum lunas dan belum tergabung ke tagihan lain.</p>
                @endforelse
            </div>
            @error('booking_ids')
                <p class="border-t border-red-100 bg-red-50 px-6 py-3 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <label for="payer_guest_id" class="mb-1.5 block text-sm font-medium text-stone-700">Pelanggan yang Membayar <span class="text-red-500">*</span></label>
            <select name="payer_guest_id" id="payer_guest_id" required class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500">
                <option value="">Pilih pembayar</option>
                @foreach($guests as $guest)
                    <option value="{{ $guest->id }}" @selected(old('payer_guest_id') == $guest->id)>{{ $guest->full_name }} — {{ $guest->email }}</option>
                @endforeach
            </select>
            @error('payer_guest_id')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-stone-500">Pembayar harus merupakan pelanggan dari salah satu booking yang dipilih.</p>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('billing-groups.index') }}" class="rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50">Batal</a>
            <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-600">Gabungkan Tagihan</button>
        </div>
    </form>
@endsection
