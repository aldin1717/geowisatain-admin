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
        return DB::transaction(function () use ($booking) {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->booking_status !== BookingStatus::CheckedIn) {
                throw new \Exception('Only checked-in bookings can be checked out.');
            }

            if ((float) $booking->grand_total > $booking->totalPaid()) {
                throw new \Exception('Cannot check-out before payment is completed.');
            }

            $selectedRoomIds = $booking->selectedRoomIds();
            $rooms = Room::whereIn('id', $selectedRoomIds)->get();
            if ($rooms->count() !== count($selectedRoomIds)) {
                throw new \Exception('One or more selected rooms or ballrooms no longer exist.');
            }

            $booking->update([
                'booking_status' => BookingStatus::CheckedOut,
                'payment_status' => PaymentStatus::Paid,
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
