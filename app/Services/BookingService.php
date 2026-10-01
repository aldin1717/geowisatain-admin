<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use App\Enums\BookingStatus;

class BookingService
{
    public function __construct(protected RoomAvailabilityService $availabilityService) {}

    public function createBooking(array $data)
    {
        return DB::transaction(function () use ($data) {
            $room = Room::findOrFail($data['room_id']);

            if (!$this->availabilityService->isAvailable($room->id, $data['check_in_date'], $data['check_out_date'])) {
                throw new \Exception('Room is not available for the selected dates.');
            }

            if ($data['num_guests'] > $room->capacity) {
                throw new \Exception('Number of guests exceeds room capacity.');
            }

            $date1 = new \DateTime($data['check_in_date']);
            $date2 = new \DateTime($data['check_out_date']);
            $numNights = $date2->diff($date1)->format("%a");

            if ($numNights <= 0) {
                throw new \Exception('Check-out date must be after check-in date.');
            }

            $roomRate = $room->price_per_night;
            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $additionalCharge = $data['additional_charge'] ?? 0;

            $grandTotal = ($roomRate * $numNights) - $discount + $tax + $additionalCharge;

            $booking = Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'guest_id' => $data['guest_id'],
                'room_id' => $room->id,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'num_guests' => $data['num_guests'],
                'num_nights' => $numNights,
                'room_rate' => $roomRate,
                'discount' => $discount,
                'tax' => $tax,
                'additional_charge' => $additionalCharge,
                'grand_total' => $grandTotal,
                'booking_status' => BookingStatus::Confirmed,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            return $booking;
        });
    }

    private function generateBookingNumber(): string
    {
        $prefix = 'BK-' . date('Y') . '-';
        $lastBooking = Booking::where('booking_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastBooking) {
            return $prefix . '00001';
        }

        $lastNumber = (int) substr($lastBooking->booking_number, -5);
        return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}
