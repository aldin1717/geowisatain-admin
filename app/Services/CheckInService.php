<?php

namespace App\Services;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckInService
{
    public function process(Booking $booking)
    {
        if ($booking->booking_status !== BookingStatus::Confirmed) {
            throw new \Exception('Only confirmed bookings can be checked in.');
        }

        $selectedRoomIds = $booking->selectedRoomIds();
        $rooms = Room::whereIn('id', $selectedRoomIds)->get();
        if ($rooms->count() !== count($selectedRoomIds)) {
            throw new \Exception('One or more selected rooms or ballrooms no longer exist.');
        }

        if ($rooms->contains(fn ($room) => in_array($room->status, [RoomStatus::Occupied, RoomStatus::Maintenance, RoomStatus::OutOfService]))) {
            throw new \Exception('One or more selected rooms or ballrooms are not available for check-in.');
        }

        return DB::transaction(function () use ($booking, $rooms) {
            $booking->update([
                'booking_status' => BookingStatus::CheckedIn,
                'actual_check_in' => Carbon::now(),
                'checked_in_by' => auth()->id()
            ]);

            $rooms->each->update([
                'status' => RoomStatus::Occupied
            ]);

            return $booking;
        });
    }
}
