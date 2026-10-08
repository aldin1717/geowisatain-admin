<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in Receipt</title>
    <style>
        @page { size: A4; margin: 18mm; }
        body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: bold; letter-spacing: 0.04em; }
        .meta { font-size: 12px; color: #6b7280; }
        .actions { margin-bottom: 20px; text-align: right; }
        .download-link { display: inline-block; padding: 9px 14px; border-radius: 6px; background: #059669; color: #ffffff; font-size: 13px; font-weight: bold; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px 6px; text-align: left; font-size: 13px; }
        .totals { margin-top: 18px; font-size: 14px; }
        .totals td { border: none; }
        .footer { margin-top: 22px; font-size: 12px; color: #6b7280; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    @unless(request()->routeIs('bookings.check-in.receipt.download'))
    <div class="actions">
        <a class="download-link" href="{{ route('bookings.check-in.receipt.download', $booking) }}">Download PDF</a>
    </div>
    @endunless

    <div class="header">
        <div class="title">CHECK-IN RECEIPT</div>
        <div class="meta">Hotel Management System</div>
    </div>

    <table>
        <tr>
            <th>Booking</th>
            <td>{{ $booking->booking_number }}</td>
        </tr>
        <tr>
            <th>Guest</th>
            <td>{{ $booking->guest->full_name }}</td>
        </tr>
        <tr>
            <th>Kamar / Ballroom</th>
            <td>
                @foreach($selectedRooms as $room)
                    {{ $room->roomType->category === 'ballroom' ? 'Ballroom' : 'Kamar' }} {{ $room->room_number }} / {{ $room->roomType->name }}@if(! $loop->last), @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <th>Check-in</th>
            <td>{{ $booking->actual_check_in ? $booking->actual_check_in->format('d M Y, H:i') : now()->format('d M Y, H:i') }}</td>
        </tr>
        <tr>
            <th>Check-out</th>
            <td>{{ $booking->check_out_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Type</th>
            <td>{{ $booking->booking_type?->label() ?? 'Umum' }}</td>
        </tr>
    </table>

    <table class="totals">
        <tr>
            <td>Room Rate</td>
            <td style="text-align:right;">Rp {{ number_format($booking->room_rate, 0, ',', '.') }}</td>
        </tr>
        @if(! empty($booking->additional_charge_breakdown))
            @foreach($booking->additional_charge_breakdown as $charge)
            <tr>
                <td>{{ $charge['name'] }}</td>
                <td style="text-align:right;">Rp {{ number_format($charge['amount'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td><strong>Total Biaya Tambahan</strong></td>
                <td style="text-align:right;"><strong>Rp {{ number_format($booking->additional_charge, 0, ',', '.') }}</strong></td>
            </tr>
        @elseif($booking->additional_charge > 0)
            <tr>
                <td>Additional Charge</td>
                <td style="text-align:right;">Rp {{ number_format($booking->additional_charge, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if($booking->ballroom_amount > 0)
        <tr>
            <td>Ballroom / Day Use</td>
            <td style="text-align:right;">Rp {{ number_format($booking->ballroom_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td><strong>Grand Total</strong></td>
            <td style="text-align:right;"><strong>Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div class="footer">
        <p>Terima kasih atas kepercayaannya.</p>
        <p>Printed: {{ now()->format('d M Y, H:i') }}</p>
    </div>
</body>
</html>
