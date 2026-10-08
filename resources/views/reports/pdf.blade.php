<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran - {{ config('app.name', 'GeowisataInn') }}</title>
    <style>
        @page { margin: 28px 30px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 9px; }
        h1 { margin: 0; font-size: 18px; }
        .muted { color: #6b7280; }
        .header { margin-bottom: 16px; }
        .summary { width: 100%; margin-bottom: 16px; border-collapse: separate; border-spacing: 6px 0; }
        .summary td { width: 33.33%; padding: 9px; border: 1px solid #e5e7eb; }
        .summary-label { display: block; margin-bottom: 5px; color: #6b7280; }
        .summary-value { font-size: 13px; font-weight: bold; }
        h2 { margin: 16px 0 7px; font-size: 12px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { padding: 6px 5px; border: 1px solid #d1d5db; text-align: left; vertical-align: top; }
        table.data th { background: #f3f4f6; }
        .right { text-align: right !important; white-space: nowrap; }
        .daily { width: 100%; border-collapse: collapse; }
        .daily td { padding: 4px 5px; border-bottom: 1px solid #e5e7eb; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'GeowisataInn') }} — Payment Report</h1>
        <p class="muted">{{ $start->format('d M Y') }} – {{ $end->format('d M Y') }} | Generated {{ now()->format('d M Y, H:i') }}</p>
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="summary-label">Total Income</span>
                <span class="summary-value">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="summary-label">Transactions</span>
                <span class="summary-value">{{ number_format($transactionCount) }}</span>
            </td>
            <td>
                <span class="summary-label">Bookings With Payments</span>
                <span class="summary-value">{{ number_format($bookingCount) }}</span>
            </td>
        </tr>
    </table>

    <h2>Income by Day</h2>
    <table class="daily">
        @foreach($dailyReport as $day)
            <tr>
                <td>{{ $day['date']->format('D, d M Y') }}</td>
                <td class="right">Rp {{ number_format($day['amount'], 0, ',', '.') }}</td>
                <td class="right">{{ $day['transactions'] }} transactions</td>
            </tr>
        @endforeach
    </table>

    <h2>Booking &amp; Payment Activity</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Activity</th>
                <th>Payment No.</th>
                <th>Booking / Guest</th>
                <th>Booking Type</th>
                <th>Room(s)</th>
                <th>Status</th>
                <th>Method</th>
                <th class="right">Amount / Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('d M Y, H:i') }}</td>
                    <td>Payment</td>
                    <td>{{ $payment->payment_number }}</td>
                    <td>
                        {{ $payment->billingGroup?->invoice_number ?? $payment->booking?->booking_number ?? 'Booking deleted' }}<br>
                        <span class="muted">{{ $payment->billingGroup?->payerGuest?->full_name ?? $payment->booking?->guest?->full_name ?? '-' }}</span>
                    </td>
                    <td>{{ $payment->billingGroup ? 'Tagihan Gabungan' : ($payment->booking?->booking_type?->label() ?? '—') }}</td>
                    <td>{{ $payment->booking?->room?->room_number ?? '—' }}</td>
                    <td>{{ $payment->payment_status?->label() ?? $payment->payment_status }}</td>
                    <td>{{ $payment->payment_method?->label() ?? $payment->payment_method }}</td>
                    <td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @foreach($bookingsWithoutPeriodPayments as $booking)
                @php
                    $selectedRooms = $booking->rooms->isNotEmpty() ? $booking->rooms : collect([$booking->room])->filter();
                    $bookingActivityDate = $booking->check_in_date->between($start, $end)
                        ? $booking->check_in_date
                        : $booking->created_at;
                @endphp
                <tr>
                    <td>{{ $bookingActivityDate->format('d M Y') }}</td>
                    <td>Booking</td>
                    <td>—</td>
                    <td>
                        {{ $booking->booking_number }}<br>
                        <span class="muted">{{ $booking->guest?->full_name ?? '-' }}</span>
                    </td>
                    <td>{{ $booking->booking_type?->label() ?? 'Umum' }}</td>
                    <td>{{ $selectedRooms->pluck('room_number')->join(', ') ?: '—' }}</td>
                    <td>{{ $booking->booking_status?->label() ?? $booking->booking_status }} · {{ $booking->payment_status?->label() ?? $booking->payment_status }}</td>
                    <td>No payment in period</td>
                    <td class="right">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
