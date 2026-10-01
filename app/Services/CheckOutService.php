<?php

namespace App\Services;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckOutService
{
    public function process(Booking $booking)
    {
        if ($booking->booking_status !== BookingStatus::CheckedIn) {
            throw new \Exception('Only checked-in bookings can be checked out.');
        }

        if ($booking->payment_status !== PaymentStatus::Paid) {
            throw new \Exception('Cannot check-out without full payment.');
        }

        return DB::transaction(function () use ($booking) {
            $booking->update([
                'booking_status' => BookingStatus::CheckedOut,
                'actual_check_out' => Carbon::now(),
                'checked_out_by' => auth()->id()
            ]);

            $booking->room->update([
                'status' => RoomStatus::Cleaning
            ]);

            return $booking;
        });
    }
}
