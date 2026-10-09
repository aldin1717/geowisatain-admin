<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('reports.index', $this->reportData($request));
    }

    public function exportPdf(Request $request)
    {
        $data = $this->reportData($request);
        $periodLabel = $data['period'] === 'week' ? 'mingguan' : 'bulanan';
        $filename = "laporan-pembayaran-{$periodLabel}-{$data['anchorDate']->format('Y-m-d')}.pdf";

        return Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    private function reportData(Request $request): array
    {
        [$period, $anchorDate, $start, $end] = $this->periodRange($request);
        $payments = $this->paymentsForRange($start, $end)
            ->with([
                'booking.guest',
                'booking.room',
                'booking.rooms',
                'billingGroup.payerGuest',
                'billingGroup.bookings.room',
                'billingGroup.bookings.rooms',
            ])
            ->orderByDesc('payment_date')
            ->get();
        $paymentReportDetails = $this->paymentReportDetails($payments);
        $bookings = Booking::query()
            ->with(['guest', 'room', 'rooms'])
            ->where(function (Builder $query) use ($start, $end): void {
                $query->whereBetween('check_in_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('created_at', [$start, $end]);
            })
            ->orderBy('check_in_date')
            ->orderBy('booking_number')
            ->get();
        $bookingsWithPayments = $payments->flatMap(function (Payment $payment) {
            if ($payment->billingGroup) {
                return $payment->billingGroup->bookings->pluck('id');
            }

            return $payment->booking_id ? [$payment->booking_id] : [];
        })->unique();
        $bookingsWithoutPeriodPayments = $bookings
            ->reject(fn (Booking $booking) => $bookingsWithPayments->contains($booking->id))
            ->values();

        $dailyPayments = $payments->groupBy(fn (Payment $payment) => $payment->payment_date->toDateString());
        $dailyReport = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dayPayments = $dailyPayments->get($date->toDateString(), collect());
            $dailyReport[] = [
                'date' => $date->copy(),
                'amount' => (float) $dayPayments->sum('amount'),
                'transactions' => $dayPayments->count(),
            ];
        }

        $summary = [
            'day' => $this->paymentsForRange($anchorDate->copy()->startOfDay(), $anchorDate->copy()->endOfDay())->sum('amount'),
            'week' => $this->paymentsForRange($anchorDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(), $anchorDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay())->sum('amount'),
            'month' => $this->paymentsForRange($anchorDate->copy()->startOfMonth()->startOfDay(), $anchorDate->copy()->endOfMonth()->endOfDay())->sum('amount'),
        ];

        return [
            'period' => $period,
            'anchorDate' => $anchorDate,
            'start' => $start,
            'end' => $end,
            'payments' => $payments,
            'paymentReportDetails' => $paymentReportDetails,
            'bookingsWithoutPeriodPayments' => $bookingsWithoutPeriodPayments,
            'totalIncome' => (float) $payments->sum('amount'),
            'transactionCount' => $payments->count(),
            'bookingCount' => $payments->flatMap(function (Payment $payment) {
                if ($payment->billingGroup) {
                    return $payment->billingGroup->bookings->pluck('id');
                }

                return $payment->booking_id ? [$payment->booking_id] : [];
            })->unique()->count(),
            'dailyReport' => $dailyReport,
            'maxDailyIncome' => max(1, ...array_column($dailyReport, 'amount')),
            'summary' => $summary,
        ];
    }

    public function export(Request $request)
    {
        [$period, $anchorDate, $start, $end] = $this->periodRange($request);
        $payments = $this->paymentsForRange($start, $end)
            ->with([
                'booking.guest',
                'booking.room',
                'booking.rooms',
                'billingGroup.payerGuest',
                'billingGroup.bookings.room',
                'billingGroup.bookings.rooms',
            ])
            ->orderBy('payment_date')
            ->get();
        $paymentReportDetails = $this->paymentReportDetails($payments);
        $periodLabel = $period === 'week' ? 'mingguan' : 'bulanan';
        $filename = "laporan-pembayaran-{$periodLabel}-{$anchorDate->format('Y-m-d')}.csv";

        return response()->streamDownload(function () use ($payments, $paymentReportDetails) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal', 'Nomor Pembayaran', 'Nomor Booking', 'Nama Tamu', 'Tipe Booking', 'Nomor Kamar', 'Status', 'Metode', 'Nominal', 'Catatan'], ';', '"', '\\');

            foreach ($payments as $payment) {
                fputcsv($output, [
                    $payment->payment_date->format('Y-m-d H:i:s'),
                    $payment->payment_number,
                    $payment->billingGroup?->invoice_number ?? $payment->booking?->booking_number,
                    $this->safeCsvText($payment->billingGroup?->payerGuest?->full_name ?? $payment->booking?->guest?->full_name),
                    $paymentReportDetails[$payment->id]['booking_type'],
                    $this->safeCsvText($paymentReportDetails[$payment->id]['rooms']),
                    $this->safeCsvText($paymentReportDetails[$payment->id]['status']),
                    $payment->payment_method->label(),
                    $payment->amount,
                    $this->safeCsvText($payment->notes),
                ], ';', '"', '\\');
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function periodRange(Request $request): array
    {
        $filters = $request->validate([
            'period' => ['nullable', 'in:week,month'],
            'date' => ['nullable', 'date'],
        ]);

        $period = $filters['period'] ?? 'week';
        $anchorDate = Carbon::parse($filters['date'] ?? today()->toDateString());

        if ($period === 'month') {
            $start = $anchorDate->copy()->startOfMonth()->startOfDay();
            $end = $anchorDate->copy()->endOfMonth()->endOfDay();
        } else {
            $start = $anchorDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $end = $anchorDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        }

        return [$period, $anchorDate, $start, $end];
    }

    private function paymentsForRange(Carbon $start, Carbon $end): Builder
    {
        return Payment::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereBetween('payment_date', [$start, $end]);
    }

    private function safeCsvText(?string $value): string
    {
        $value = $value ?? '';

        return preg_match('/^[\t\r ]*[=+@-]/', $value) === 1 ? "'{$value}" : $value;
    }

    private function paymentReportDetails(Collection $payments): array
    {
        return $payments->mapWithKeys(function (Payment $payment): array {
            $bookings = $payment->billingGroup?->bookings ?? collect([$payment->booking])->filter();
            $rooms = $bookings->flatMap(function (Booking $booking) {
                return $booking->rooms->isNotEmpty()
                    ? $booking->rooms
                    : collect([$booking->room])->filter();
            })->pluck('room_number')->filter()->unique()->values()->join(', ');
            $statuses = $bookings->map(function (Booking $booking) use ($payment): string {
                $bookingStatus = $booking->booking_status?->label() ?? $booking->booking_status ?? '—';
                $paymentStatus = $booking->payment_status?->label() ?? $booking->payment_status ?? '—';
                $status = "{$bookingStatus} · {$paymentStatus}";

                return $payment->billingGroup
                    ? "{$booking->booking_number}: {$status}"
                    : $status;
            })->join('; ');
            $bookingTypes = $bookings
                ->map(fn (Booking $booking) => $booking->booking_type?->label() ?? 'Umum')
                ->unique()
                ->values()
                ->join(', ');

            return [$payment->id => [
                'booking_type' => $bookingTypes ?: '—',
                'rooms' => $rooms ?: '—',
                'status' => $statuses ?: '—',
            ]];
        })->all();
    }
}