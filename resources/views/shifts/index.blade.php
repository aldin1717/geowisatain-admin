@extends('layouts.app')

@section('title', 'Rekap Shift')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-stone-900">Pemeriksaan & Rekap Shift</h1>
        <p class="mt-1 text-sm text-stone-500">Buka shift sebelum mencatat pembayaran, lalu cocokkan kas saat pergantian petugas.</p>
    </div>
    @error('shift')
        <p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
    @enderror

    @if($activeShift)
        <section class="rounded-xl border border-emerald-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-emerald-700">Shift sedang aktif</p>
                    <p class="mt-1 text-lg font-semibold text-stone-900">
                        Dibuka {{ $activeShift->opened_at->format('d M Y, H:i') }}
                    </p>
                    <p class="text-sm text-stone-500">Petugas yang bertugas: {{ $activeShift->attendant_name ?? $activeShift->openedBy?->name ?? 'Tidak diketahui' }}</p>
                    <p class="mt-1 text-xs text-stone-400">Shift dibuka oleh akun {{ $activeShift->openedBy?->name ?? 'Tidak diketahui' }}</p>
                    <p class="mt-1 text-sm text-stone-600">Kas awal: Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</p>
                </div>
                <a href="{{ route('shifts.show', $activeShift) }}" class="inline-flex justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-500">
                    Periksa transaksi shift
                </a>
            </div>
        </section>
    @else
        <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-stone-900">Buka shift baru</h2>
            <p class="mt-1 text-sm text-stone-500">Hitung dan masukkan uang tunai awal sebelum mulai mencatat pembayaran.</p>
            <form method="POST" action="{{ route('shifts.open') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="w-full sm:max-w-xs">
                    <label for="attendant_name" class="mb-1 block text-sm font-medium text-stone-700">Nama petugas yang bertugas</label>
                    <input id="attendant_name" name="attendant_name" type="text" maxlength="255" value="{{ old('attendant_name') }}" required class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('attendant_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="w-full sm:max-w-xs">
                    <label for="opening_cash" class="mb-1 block text-sm font-medium text-stone-700">Kas awal (Rp)</label>
                    <input id="opening_cash" name="opening_cash" type="number" min="0" step="0.01" value="{{ old('opening_cash', 0) }}" required class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('opening_cash')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-500">Buka Shift</button>
            </form>
            @error('shift')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
        </section>
    @endif

    <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-200 px-5 py-4">
            <h2 class="font-semibold text-stone-900">Riwayat shift</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Waktu shift</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Petugas bertugas</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Dibuka / ditutup oleh</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-stone-500">Kas awal</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-stone-500">Kas akhir</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-stone-500">Selisih kas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($recentShifts as $shift)
                        <tr>
                            <td class="px-5 py-3 text-sm">
                                <a href="{{ route('shifts.show', $shift) }}" class="font-medium text-primary-700 hover:underline">
                                    {{ $shift->opened_at->format('d M Y, H:i') }} – {{ $shift->closed_at->format('d M Y, H:i') }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-sm text-stone-600">{{ $shift->attendant_name ?? $shift->openedBy?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-stone-600">{{ $shift->openedBy?->name ?? '—' }} → {{ $shift->closedBy?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-sm text-stone-600">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-sm text-stone-600">Rp {{ number_format($shift->closing_cash, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium {{ (float) $shift->cash_variance === 0.0 ? 'text-emerald-700' : 'text-red-700' }}">Rp {{ number_format($shift->cash_variance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-stone-500">Belum ada shift yang ditutup.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recentShifts->hasPages())
            <div class="border-t border-stone-200 px-5 py-4">{{ $recentShifts->links() }}</div>
        @endif
    </section>
</div>
@endsection
