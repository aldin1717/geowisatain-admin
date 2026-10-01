<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function processPayment(Booking $booking, array $data)
    {
        return DB::transaction(function () use ($booking, $data) {
            $payment = Payment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'booking_id' => $booking->id,
                'payment_date' => now(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_status' => PaymentStatus::Paid,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            $totalPaid = $booking->payments()->sum('amount');
            
            if ($totalPaid >= $booking->grand_total) {
                $booking->update(['payment_status' => PaymentStatus::Paid]);
            } else {
                $booking->update(['payment_status' => PaymentStatus::Partial]);
            }

            return $payment;
        });
    }

    private function generatePaymentNumber(): string
    {
        $prefix = 'PAY-' . date('Y') . '-';
        $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastPayment) {
            return $prefix . '00001';
        }

        $lastNumber = (int) substr($lastPayment->payment_number, -5);
        return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}
