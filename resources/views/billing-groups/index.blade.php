@extends('layouts.app')

@section('title', 'Tagihan Gabungan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Tagihan Gabungan</h1>
            <p class="mt-1 text-sm text-stone-500">Gabungkan beberapa booking agar dapat dibayar oleh satu orang.</p>
        </div>
        <a href="{{ route('billing-groups.create') }}" class="rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-600">
            Buat Tagihan Gabungan
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-stone-500">No. Tagihan</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-stone-500">Pembayar</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-stone-500">Booking</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase text-stone-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-stone-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($billingGroups as $billingGroup)
                        <tr>
                            <td class="px-6 py-4">
                                <a href="{{ route('billing-groups.show', $billingGroup) }}" class="font-medium text-primary-700 hover:underline">{{ $billingGroup->invoice_number }}</a>
                            </td>
                            <td class="px-6 py-4 text-stone-700">{{ $billingGroup->payerGuest->full_name }}</td>
                            <td class="px-6 py-4 text-stone-600">{{ $billingGroup->bookings_count }}</td>
                            <td class="px-6 py-4 capitalize text-stone-600">{{ $billingGroup->payment_status }}</td>
                            <td class="px-6 py-4 text-right font-medium text-stone-900">Rp {{ number_format($billingGroup->totalAmount(), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-stone-500">Belum ada tagihan gabungan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($billingGroups->hasPages())
            <div class="border-t border-stone-200 px-6 py-4">{{ $billingGroups->links() }}</div>
        @endif
    </div>
@endsection
