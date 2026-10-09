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
    public function process(Booking $booking, bool $early = false, ?string $reason = null)
    {
        return DB::transaction(function () use ($booking, $early, $reason) {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->booking_status !== BookingStatus::CheckedIn) {
                throw new \Exception('Only checked-in bookings can be checked out.');
            }

            $isBeforeScheduledCheckout = $booking->check_out_date->isAfter(today());
            if ($early && ! $isBeforeScheduledCheckout) {
                throw new \Exception('Early check-out is only available before the scheduled check-out date.');
            }
            if ($early && blank($reason)) {
                throw new \Exception('An early check-out reason is required.');
            }
            if (! $early && $isBeforeScheduledCheckout) {
                throw new \Exception('This booking is scheduled to check out later. Use Early Check-out to proceed before that date.');
            }

            if ((float) $booking->grand_total > $booking->totalPaid()) {
                throw new \Exception('Cannot check-out before payment is completed.');
            }

            $selectedRoomIds = $booking->selectedRoomIds();
            $rooms = Room::whereIn('id', $selectedRoomIds)->lockForUpdate()->get();
            if ($rooms->count() !== count($selectedRoomIds)) {
                throw new \Exception('One or more selected rooms or ballrooms no longer exist.');
            }

            $booking->update([
                'booking_status' => BookingStatus::CheckedOut,
                'payment_status' => PaymentStatus::Paid,
                'actual_check_out' => Carbon::now(),
                'is_early_check_out' => $early,
                'notes' => $early
                    ? trim(($booking->notes ? $booking->notes."\n\n" : '').'Early check-out reason: '.$reason)
                    : $booking->notes,
                'checked_out_by' => auth()->id()
            ]);

            $rooms->each->update([
                'status' => RoomStatus::Cleaning
            ]);

            return $booking;
        });
    }
}
