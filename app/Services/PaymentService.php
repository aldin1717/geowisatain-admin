<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\BillingGroup;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Shift;
use DomainException;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function processPayment(Booking $booking, array $data)
    {
        return DB::transaction(function () use ($booking, $data) {
            $shift = $this->activeShiftForPayment();
            $payment = Payment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'booking_id' => $booking->id,
                'shift_id' => $shift->id,
                'payment_date' => now(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_status' => PaymentStatus::Paid,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $totalPaid = $booking->totalPaid();

            if ($totalPaid >= $booking->grand_total) {
                $booking->update(['payment_status' => PaymentStatus::Paid]);
            } else {
                $booking->update(['payment_status' => PaymentStatus::Partial]);
            }

            return $payment;
        });
    }

    public function processBillingGroupPayment(BillingGroup $billingGroup, array $data): Payment
    {
        return DB::transaction(function () use ($billingGroup, $data) {
            $shift = $this->activeShiftForPayment();
            $billingGroup = BillingGroup::query()->lockForUpdate()->findOrFail($billingGroup->id);
            $bookings = $billingGroup->bookings()->orderBy('id')->lockForUpdate()->get();
            $remaining = $billingGroup->outstandingAmount();
            $amount = (float) $data['amount'];

            if ($amount > $remaining) {
                throw new \InvalidArgumentException('Payment amount exceeds the outstanding merged bill balance.');
            }

            $payment = Payment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'billing_group_id' => $billingGroup->id,
                'shift_id' => $shift->id,
                'payment_date' => now(),
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_status' => PaymentStatus::Paid,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $unallocatedAmount = $amount;

            foreach ($bookings as $booking) {
                $bookingBalance = max((float) $booking->grand_total - $booking->totalPaid(), 0);
                $allocation = min($bookingBalance, $unallocatedAmount);

                if ($allocation <= 0) {
                    continue;
                }

                DB::table('booking_payment_allocations')->insert([
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id,
                    'amount' => $allocation,
                ]);

                $booking->update([
                    'payment_status' => $booking->totalPaid() >= (float) $booking->grand_total
                        ? PaymentStatus::Paid
                        : PaymentStatus::Partial,
                ]);

                $unallocatedAmount -= $allocation;
            }

            $billingGroup->update([
                'payment_status' => $billingGroup->outstandingAmount() <= 0
                    ? PaymentStatus::Paid->value
                    : PaymentStatus::Partial->value,
            ]);

            return $payment;
        });
    }

    private function activeShiftForPayment(): Shift
    {
        $shift = Shift::query()
            ->whereNotNull('active_key')
            ->lockForUpdate()
            ->first();

        if (! $shift) {
            throw new DomainException('Buka shift aktif sebelum mencatat pembayaran.');
        }

        return $shift;
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
