<?php

namespace App\Services;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckInService
{
    public function process(Booking $booking)
    {
        if ($booking->booking_status !== BookingStatus::Confirmed) {
            throw new \Exception('Only confirmed bookings can be checked in.');
        }

        if (in_array($booking->room->status, [RoomStatus::Occupied, RoomStatus::Maintenance, RoomStatus::OutOfService])) {
            throw new \Exception('Room is not available for check-in.');
        }

        return DB::transaction(function () use ($booking) {
            $booking->update([
                'booking_status' => BookingStatus::CheckedIn,
                'actual_check_in' => Carbon::now(),
                'checked_in_by' => auth()->id()
            ]);

            $booking->room->update([
                'status' => RoomStatus::Occupied
            ]);

            return $booking;
        });
    }
}
