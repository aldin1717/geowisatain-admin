<?php

namespace App\Services;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\PaymentStatus;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckOutService
{
    public function process(Booking $booking)
    {
        if ($booking->booking_status !== BookingStatus::CheckedIn) {
            throw new \Exception('Only checked-in bookings can be checked out.');
        }

        if ($booking->payment_status === PaymentStatus::Unpaid || $booking->payment_status === PaymentStatus::Partial) {
            throw new \Exception('Cannot check-out before payment is completed.');
        }

        $selectedRoomIds = $booking->selectedRoomIds();
        $rooms = Room::whereIn('id', $selectedRoomIds)->get();
        if ($rooms->count() !== count($selectedRoomIds)) {
            throw new \Exception('One or more selected rooms or ballrooms no longer exist.');
        }

        return DB::transaction(function () use ($booking, $rooms) {
            $booking->update([
                'booking_status' => BookingStatus::CheckedOut,
                'actual_check_out' => Carbon::now(),
                'checked_out_by' => auth()->id()
            ]);

            $rooms->each->update([
                'status' => RoomStatus::Cleaning
            ]);

            return $booking;
        });
    }
}
