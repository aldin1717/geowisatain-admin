@extends('layouts.app')

@section('title', 'Payment Reports')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Payment Reports</h1>
            <p class="mt-1 text-sm text-stone-500">{{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}</p>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="period" class="mb-1 block text-xs font-medium text-stone-600">Period</label>
                    <select id="period" name="period" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 focus:border-primary-500 focus:ring-primary-500">
                        <option value="week" @selected($period === 'week')>Weekly</option>
                        <option value="month" @selected($period === 'month')>Monthly</option>
                    </select>
                </div>
                <div>
                    <label for="date" class="mb-1 block text-xs font-medium text-stone-600">Reference date</label>
                    <input id="date" name="date" type="date" value="{{ $anchorDate->format('Y-m-d') }}" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 focus:border-primary-500 focus:ring-primary-500">
                </div>
                <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-500">Apply</button>
            </form>

            <a href="{{ route('reports.export', ['period' => $period, 'date' => $anchorDate->format('Y-m-d')]) }}" class="inline-flex items-center gap-2 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l4-4m-4 4l-4-4m-4 6v3h16v-3"></path></svg>
                Export CSV
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Total Income</p>
            <p class="mt-2 text-2xl font-semibold text-stone-900">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Transactions</p>
            <p class="mt-2 text-2xl font-semibold text-stone-900">{{ number_format($transactionCount) }}</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Bookings With Payments</p>
            <p class="mt-2 text-2xl font-semibold text-stone-900">{{ number_format($bookingCount) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-stone-200 bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-stone-500">Hari ini</p>
            <p class="mt-2 text-xl font-semibold text-stone-900">Rp {{ number_format($summary['day'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-stone-500">Minggu ini</p>
            <p class="mt-2 text-xl font-semibold text-stone-900">Rp {{ number_format($summary['week'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-stone-500">Bulan ini</p>
            <p class="mt-2 text-xl font-semibold text-stone-900">Rp {{ number_format($summary['month'], 0, ',', '.') }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-lg border border-stone-200 bg-white">
        <div class="border-b border-stone-200 px-5 py-4">
            <h2 class="font-semibold text-stone-900">Income by Day</h2>
        </div>
        <div class="divide-y divide-stone-100">
            @foreach($dailyReport as $day)
                <div class="grid grid-cols-[7rem_minmax(0,1fr)_auto] items-center gap-4 px-5 py-3">
                    <span class="text-sm text-stone-600">{{ $day['date']->format('D, d M') }}</span>
                    <div class="h-2 overflow-hidden rounded-full bg-stone-100">
                        <div class="h-full rounded-full bg-primary-500" style="width: {{ ($day['amount'] / $maxDailyIncome) * 100 }}%"></div>
                    </div>
                    <div class="min-w-32 text-right">
                        <span class="text-sm font-medium text-stone-900">Rp {{ number_format($day['amount'], 0, ',', '.') }}</span>
                        <span class="ml-1 text-xs text-stone-500">({{ $day['transactions'] }})</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-stone-200 bg-white">
        <div class="border-b border-stone-200 px-5 py-4">
            <h2 class="font-semibold text-stone-900">Payment Transactions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Payment No.</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Booking / Guest</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-stone-500">Method</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-stone-500">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-stone-600">{{ $payment->payment_date->format('d M Y, H:i') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-primary-700">{{ $payment->payment_number }}</td>
                            <td class="px-5 py-3 text-sm">
                                <p class="font-medium text-stone-800">{{ $payment->billingGroup?->invoice_number ?? $payment->booking?->booking_number ?? 'Booking deleted' }}</p>
                                <p class="text-stone-500">{{ $payment->billingGroup?->payerGuest?->full_name ?? $payment->booking?->guest?->full_name ?? '-' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-stone-600">{{ $payment->payment_method->label() }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-medium text-stone-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-stone-500">No payment transactions in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection