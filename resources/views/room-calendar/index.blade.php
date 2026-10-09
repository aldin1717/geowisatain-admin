@extends('layouts.app')

@section('title', 'Room Calendar')

@section('content')
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Room Calendar</h1>
            <p class="mt-1 text-sm text-stone-500">Pilih lantai untuk melihat ketersediaan kamar per tanggal.</p>
        </div>
        <form method="GET" action="{{ route('room-calendar.index') }}" class="flex flex-wrap items-end gap-2">
            <label class="text-sm font-medium text-stone-700">
                Bulan
                <input type="month" name="month" value="{{ $month->format('Y-m') }}"
                    class="mt-1 block rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
            </label>
            <label class="text-sm font-medium text-stone-700">
                Lantai
                <select name="floor" onchange="this.form.submit()" class="mt-1 block min-w-36 rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua lantai</option>
                    @foreach($floorOptions as $floor)
                        <option value="{{ $floor }}" @selected($selectedFloor === (string) $floor)>Lantai {{ $floor }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="rounded-lg bg-primary-700 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600">Tampilkan</button>
        </form>
    </div>

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('room-calendar.index', ['month' => $month->subMonth()->format('Y-m'), 'floor' => $selectedFloor]) }}"
                class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 hover:bg-stone-50" aria-label="Bulan sebelumnya">
                &larr; Sebelumnya
            </a>
            <h2 class="min-w-28 text-center font-semibold text-stone-900">{{ $month->translatedFormat('F Y') }}</h2>
            <a href="{{ route('room-calendar.index', ['month' => $month->addMonth()->format('Y-m'), 'floor' => $selectedFloor]) }}"
                class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 hover:bg-stone-50" aria-label="Bulan berikutnya">
                Berikutnya &rarr;
            </a>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-xs text-stone-600">
            <span><span class="text-emerald-700">●</span> Tersedia</span>
            <span><span class="text-blue-700">●</span> Booking</span>
            <span><span class="text-amber-700">●</span> Check-in</span>
            <span><span class="text-purple-700">●</span> Perawatan</span>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-max border-collapse text-left text-xs">
                <thead class="sticky top-0 z-10 bg-stone-50">
                    <tr>
                        <th class="sticky left-0 z-20 min-w-32 border-b border-r border-stone-200 bg-stone-50 px-3 py-2 text-xs font-semibold text-stone-500">Kamar</th>
                        @foreach($days as $day)
                            <th class="min-w-9 border-b border-stone-200 px-0.5 py-2 text-center {{ $day->isToday() ? 'bg-primary-50 text-primary-700' : 'text-stone-500' }}" title="{{ $day->translatedFormat('l, d F Y') }}">
                                <span class="block">{{ $day->translatedFormat('D') }}</span>
                                <span class="block font-semibold">{{ $day->format('d') }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($calendar as $row)
                        <tr>
                            <th scope="row" class="sticky left-0 z-[1] border-r border-stone-200 bg-white px-3 py-2 font-medium text-stone-900" title="{{ $row['room']->roomType->name }}">
                                {{ $row['room']->room_number }}
                            </th>
                            @foreach($row['cells'] as $cell)
                                @php
                                    $statusClass = match($cell['status']) {
                                        'reserved' => 'bg-blue-100 text-blue-800',
                                        'occupied' => 'bg-amber-100 text-amber-800',
                                        'cleaning', 'maintenance', 'out_of_service' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-emerald-50 text-emerald-800',
                                    };
                                    $statusLabel = match($cell['status']) {
                                        'reserved' => 'Booking',
                                        'occupied' => 'Check-in',
                                        'cleaning' => 'Cleaning',
                                        'maintenance' => 'Maintenance',
                                        'out_of_service' => 'Tidak aktif',
                                        default => 'Tersedia',
                                    };
                                @endphp
                                <td class="border-l border-stone-100 p-0.5 text-center">
                                    @if($cell['booking'])
                                        <a href="{{ route('bookings.show', $cell['booking']) }}"
                                            title="{{ $cell['booking']->guest->full_name }} · {{ $cell['booking']->booking_number }}"
                                            class="block rounded px-0.5 py-1.5 font-medium {{ $statusClass }}">
                                            {{ $cell['booking']->booking_status === \App\Enums\BookingStatus::CheckedIn ? 'CI' : 'B' }}
                                        </a>
                                    @else
                                        <span title="{{ $statusLabel }}" class="block rounded px-0.5 py-1.5 {{ $statusClass }}">
                                            {{ $cell['status'] === 'available' ? '·' : '!' }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($days) + 1 }}" class="px-6 py-12 text-center text-sm text-stone-500">Belum ada kamar aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
