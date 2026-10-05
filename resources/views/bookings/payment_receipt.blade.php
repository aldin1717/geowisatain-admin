<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->payment_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: bold; letter-spacing: 0.04em; }
        .meta { font-size: 12px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px 6px; text-align: left; font-size: 13px; }
        .totals { margin-top: 18px; font-size: 14px; }
        .totals td { border: none; }
        .footer { margin-top: 30px; font-size: 12px; color: #6b7280; text-align: center; }
        .status-paid { color: #059669; font-weight: bold; border: 1px solid #059669; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 8px;}
        .notes-box { margin-top: 20px; font-size: 12px; background: #f9fafb; padding: 10px; border: 1px solid #e5e7eb; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">OFFICIAL RECEIPT</div>
        <div class="meta">Bukti Pembayaran Hotel</div>
    </div>

    <table>
        <tr>
            <th style="width: 35%;">No. Kwitansi</th>
            <td>{{ $payment->payment_number }}</td>
        </tr>
        <tr>
            <th>Tanggal Bayar</th>
            <td>{{ $payment->payment_date->format('d M Y, H:i') }}</td>
        </tr>
        <tr>
            <th>No. Booking</th>
            <td>{{ $booking->booking_number }}</td>
        </tr>
        <tr>
            <th>Nama Tamu</th>
            <td>{{ $booking->guest->full_name }}</td>
        </tr>
        <tr>
            <th>Metode Pembayaran</th>
            <td>{{ $payment->payment_method->label() }}</td>
        </tr>
    </table>

    <table class="totals">
        <tr>
            <td>Total Tagihan (Grand Total)</td>
            <td style="text-align:right;">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Nominal Dibayar (Kwitansi Ini)</strong></td>
            <td style="text-align:right; font-size: 16px;">
                <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                <span class="status-paid">LUNAS</span>
            </td>
        </tr>
        <tr>
            @php
                $totalPaid = $booking->totalPaid();
                $outstanding = max((float) $booking->grand_total - $totalPaid, 0);
            @endphp
            <td style="padding-top: 12px; color: #6b7280;">Sisa Tagihan (Outstanding)</td>
            <td style="padding-top: 12px; text-align:right; color: #6b7280;">Rp {{ number_format($outstanding, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($payment->notes)
    <div class="notes-box">
        <strong>Catatan:</strong> {{ $payment->notes }}
    </div>
    @endif

    <div class="footer">
        <p>Terima kasih atas pembayaran Anda.</p>
        <p>Printed: {{ now()->format('d M Y, H:i') }}</p>
    </div>
</body>
</html>