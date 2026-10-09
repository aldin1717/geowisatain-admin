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
    public function __construct(protected RoomAvailabilityService $availabilityService) {}

    public function process(Booking $booking)
    {
        return DB::transaction(function () use ($booking) {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->booking_status !== BookingStatus::Confirmed) {
                throw new \Exception('Only confirmed bookings can be checked in.');
            }

            if ((float) $booking->grand_total > $booking->totalPaid()) {
                throw new \Exception('Cannot check-in before payment is completed.');
            }

            $selectedRoomIds = $booking->selectedRoomIds();
            $rooms = Room::whereIn('id', $selectedRoomIds)->lockForUpdate()->get();
            if ($rooms->count() !== count($selectedRoomIds)) {
                throw new \Exception('One or more selected rooms or ballrooms no longer exist.');
            }

            foreach ($selectedRoomIds as $roomId) {
                if (! $this->availabilityService->isAvailable(
                    $roomId,
                    $booking->check_in_date->toDateString(),
                    $booking->check_out_date->toDateString(),
                    $booking->id
                )) {
                    throw new \Exception('One or more selected rooms or ballrooms are not available for these dates.');
                }
            }

            if ($rooms->contains(fn ($room) => in_array($room->status, [
                RoomStatus::Occupied,
                RoomStatus::Cleaning,
                RoomStatus::Maintenance,
                RoomStatus::OutOfService,
            ], true))) {
                throw new \Exception('One or more selected rooms or ballrooms are not ready for check-in.');
            }

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
