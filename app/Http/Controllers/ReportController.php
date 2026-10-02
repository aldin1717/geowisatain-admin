<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$period, $anchorDate, $start, $end] = $this->periodRange($request);
        $payments = $this->paymentsForRange($start, $end)
            ->with(['booking.guest', 'booking.room'])
            ->orderByDesc('payment_date')
            ->get();

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

        return view('reports.index', [
            'period' => $period,
            'anchorDate' => $anchorDate,
            'start' => $start,
            'end' => $end,
            'payments' => $payments,
            'totalIncome' => (float) $payments->sum('amount'),
            'transactionCount' => $payments->count(),
            'bookingCount' => $payments->pluck('booking_id')->unique()->count(),
            'dailyReport' => $dailyReport,
            'maxDailyIncome' => max(1, ...array_column($dailyReport, 'amount')),
        ]);
    }

    public function export(Request $request)
    {
        [$period, $anchorDate, $start, $end] = $this->periodRange($request);
        $payments = $this->paymentsForRange($start, $end)
            ->with(['booking.guest', 'booking.room'])
            ->orderBy('payment_date')
            ->get();
        $periodLabel = $period === 'week' ? 'mingguan' : 'bulanan';
        $filename = "laporan-pembayaran-{$periodLabel}-{$anchorDate->format('Y-m-d')}.csv";

        return response()->streamDownload(function () use ($payments) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal', 'Nomor Pembayaran', 'Nomor Booking', 'Nama Tamu', 'Nomor Kamar', 'Metode', 'Nominal', 'Catatan'], ';', '"', '\\');

            foreach ($payments as $payment) {
                fputcsv($output, [
                    $payment->payment_date->format('Y-m-d H:i:s'),
                    $payment->payment_number,
                    $payment->booking?->booking_number,
                    $this->safeCsvText($payment->booking?->guest?->full_name),
                    $this->safeCsvText($payment->booking?->room?->room_number),
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
}