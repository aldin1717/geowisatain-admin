@extends('layouts.app')

@section('title', 'Rekap Shift')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('shifts.index') }}" class="text-sm text-stone-500 hover:text-primary-700">Rekap Shift</a>
            <h1 class="mt-1 text-2xl font-semibold text-stone-900">Pemeriksaan Transaksi Shift</h1>
            <p class="mt-1 text-sm text-stone-500">
                Petugas yang bertugas: <span class="font-medium text-stone-700">{{ $shift->attendant_name ?? $shift->openedBy?->name ?? 'Tidak diketahui' }}</span>
                · Dibuka {{ $shift->opened_at->format('d M Y, H:i') }} oleh {{ $shift->openedBy?->name ?? 'Tidak diketahui' }}
                @if($shift->closed_at)
                    · Ditutup {{ $shift->closed_at->format('d M Y, H:i') }} oleh {{ $shift->closedBy?->name ?? 'Tidak diketahui' }}
                @else
                    · <span class="font-medium text-emerald-700">Aktif</span>
                @endif
            </p>
        </div>
        <a href="{{ route('shifts.index') }}" class="text-sm font-medium text-primary-700 hover:underline">Kembali ke daftar shift</a>
    </div>
    @error('shift')
        <p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
    @enderror

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-lg border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Kas awal</p>
            <p class="mt-2 text-xl font-semibold text-stone-900">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</p>
        </div>
        @foreach(\App\Enums\PaymentMethod::cases() as $method)
            <div class="rounded-lg border border-stone-200 bg-white p-5">
                <p class="text-sm text-stone-500">Pembayaran {{ $method->label() }}</p>
                <p class="mt-2 text-xl font-semibold text-stone-900">Rp {{ number_format($methodTotals[$method->value], 0, ',', '.') }}</p>
            </div>
        @endforeach
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-5">
            <p class="text-sm text-blue-700">Kas yang seharusnya</p>
            <p class="mt-2 text-xl font-semibold text-stone-900">Rp {{ number_format($shift->expected_cash ?? $expectedCash, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-stone-500">Kas awal + pembayaran tunai</p>
        </div>
        @if($shift->closed_at)
            <div class="rounded-lg border border-stone-200 bg-white p-5">
                <p class="text-sm text-stone-500">Kas fisik saat tutup</p>
                <p class="mt-2 text-xl font-semibold text-stone-900">Rp {{ number_format($shift->closing_cash, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg border {{ (float) $shift->cash_variance === 0.0 ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }} p-5">
                <p class="text-sm text-stone-600">Selisih kas</p>
                <p class="mt-2 text-xl font-semibold {{ (float) $shift->cash_variance === 0.0 ? 'text-emerald-700' : 'text-red-700' }}">Rp {{ number_format($shift->cash_variance, 0, ',', '.') }}</p>
            </div>
        @endif
    </div>

    @if(!$shift->closed_at)
        <section class="rounded-xl border border-amber-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-stone-900">Tutup shift dan cocokkan kas</h2>
            <p class="mt-1 text-sm text-stone-500">Hitung uang tunai fisik. Sistem akan menyimpan kas yang seharusnya dan selisihnya.</p>
            <form method="POST" action="{{ route('shifts.close', $shift) }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label for="closing_cash" class="mb-1 block text-sm font-medium text-stone-700">Kas fisik saat tutup (Rp)</label>
                    <input id="closing_cash" name="closing_cash" type="number" min="0" step="0.01" value="{{ old('closing_cash') }}" required class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('closing_cash')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="closing_notes" class="mb-1 block text-sm font-medium text-stone-700">Catatan pemeriksaan (opsional)</label>
                    <textarea id="closing_notes" name="closing_notes" rows="2" class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('closing_notes') }}</textarea>
                    @error('closing_notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-amber-500">Tutup Shift</button>
                </div>
            </form>
        </section>
    @elseif($shift->closing_notes)
        <section class="rounded-xl border border-stone-200 bg-white p-5">
            <h2 class="font-semibold text-stone-900">Catatan pemeriksaan</h2>
            <p class="mt-2 whitespace-pre-line text-sm text-stone-600">{{ $shift->closing_notes }}</p>
        </section>
    @endif

    <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-200 px-5 py-4">
            <h2 class="font-semibold text-stone-900">Daftar transaksi ({{ $shift->payments->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">No. pembayaran</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Booking / Tagihan</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Petugas</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Metode</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-stone-500">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($shift->payments as $payment)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-stone-600">{{ $payment->payment_date->format('d M Y, H:i') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-primary-700">{{ $payment->payment_number }}</td>
                            <td class="px-5 py-3 text-sm text-stone-700">
                                {{ $payment->billingGroup?->invoice_number ?? $payment->booking?->booking_number ?? 'Booking dihapus' }}
                                <span class="block text-xs text-stone-500">{{ $payment->billingGroup?->payerGuest?->full_name ?? $payment->booking?->guest?->full_name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-stone-600">{{ $payment->creator?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-stone-600">{{ $payment->payment_method?->label() ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-medium text-stone-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-stone-500">Belum ada transaksi pada shift ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
