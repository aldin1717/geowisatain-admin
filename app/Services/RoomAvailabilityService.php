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

        if (in_array($room->status, [RoomStatus::Maintenance, RoomStatus::OutOfService])) {
            return false;
        }

        $query = Booking::where(function ($bookingQuery) use ($roomId) {
            $bookingQuery->where('room_id', $roomId)
                ->orWhereHas('rooms', fn ($rooms) => $rooms->where('rooms.id', $roomId));
        })
            ->whereIn('booking_status', [BookingStatus::Confirmed, BookingStatus::CheckedIn])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in_date', '<=', $checkIn)
                         ->where('check_out_date', '>=', $checkOut);
                  });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->doesntExist();
    }
}
