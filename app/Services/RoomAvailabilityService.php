<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Booking;
use App\Enums\RoomStatus;
use App\Enums\BookingStatus;

class RoomAvailabilityService
{
    public function isAvailable($roomId, $checkIn, $checkOut, $excludeBookingId = null): bool
    {
        $room = Room::findOrFail($roomId);

        if (in_array($room->status, [
            RoomStatus::Cleaning,
            RoomStatus::Maintenance,
            RoomStatus::OutOfService,
        ], true)) {
            return false;
        }

        $query = Booking::where(function ($bookingQuery) use ($roomId) {
            $bookingQuery->where('room_id', $roomId)
                ->orWhereHas('rooms', fn ($rooms) => $rooms->where('rooms.id', $roomId));
        })
            ->whereIn('booking_status', [BookingStatus::Confirmed, BookingStatus::CheckedIn])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->doesntExist();
    }
}
