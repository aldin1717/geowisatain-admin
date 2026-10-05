<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(protected RoomAvailabilityService $availabilityService) {}

    public function createBooking(array $data)
    {
        return DB::transaction(function () use ($data) {
            $selectedRoomIds = array_values(array_unique(array_map('intval', $data['room_ids'] ?? [])));
            if (empty($selectedRoomIds)) {
                throw new \Exception('Select at least one room or ballroom.');
            }

            $rooms = Room::with('roomType')->whereIn('id', $selectedRoomIds)->where('is_active', true)->get();
            if ($rooms->count() !== count($selectedRoomIds)) {
                throw new \Exception('One of the selected rooms or ballrooms is no longer active.');
            }

            $primaryRoom = $rooms->firstWhere('id', $selectedRoomIds[0]);
            $checkInDate = $data['check_in_date'];
            $checkOutDate = $data['check_out_date'];

            foreach ($rooms as $room) {
                if (! $this->availabilityService->isAvailable($room->id, $checkInDate, $checkOutDate)) {
                    throw new \Exception('One of the selected rooms is not available for the selected dates.');
                }
            }

            $hotelRooms = $rooms->filter(fn (Room $room) => $room->roomType->category === 'room');
            $totalCapacity = $hotelRooms->sum('capacity');
            if ($hotelRooms->isNotEmpty() && ($data['num_guests'] ?? 1) > $totalCapacity) {
                throw new \Exception('Number of guests exceeds the total selected room capacity.');
            }

            $guest = Guest::where('email', $data['guest_email'])->first()
                ?? Guest::where('phone', $data['guest_phone'])->first();
            $guestData = [
                'full_name' => $data['guest_full_name'],
                'phone' => $data['guest_phone'],
                'email' => $data['guest_email'],
                'address' => $data['guest_address'] ?? null,
            ];

            if ($guest) {
                $guest->update($guestData);
            } else {
                $guest = Guest::create($guestData + ['guest_code' => $this->generateGuestCode()]);
            }

            $date1 = new \DateTime($checkInDate);
            $date2 = new \DateTime($checkOutDate);
            $numNights = $date2->diff($date1)->format('%a');

            if ($numNights <= 0) {
                throw new \Exception('Check-out date must be after check-in date.');
            }

            $bookingType = $data['booking_type'] ?? 'general';
            $roomRate = $bookingType === 'diklat' ? 0 : $hotelRooms->sum(fn (Room $room) => $room->price_per_night);
            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $additionalChargeBreakdown = is_array($data['additional_charge_breakdown'] ?? null)
                ? array_values($data['additional_charge_breakdown'])
                : [];
            $additionalCharge = array_key_exists('additional_charge_breakdown', $data)
                ? array_sum(array_map(static fn ($item) => (float) $item['amount'], $additionalChargeBreakdown))
                : (float) ($data['additional_charge'] ?? 0);
            $ballroomAmount = (float) ($data['ballroom_amount'] ?? 0);
            $hasBallroom = $rooms->contains(fn (Room $room) => $room->roomType->category === 'ballroom');
            $isDayUse = (bool) ($data['is_day_use'] ?? false) || $ballroomAmount > 0 || $hasBallroom;
            $isBillMerged = (bool) ($data['is_bill_merged'] ?? false);

            $grandTotal = ($roomRate * $numNights) - $discount + $tax + $additionalCharge + $ballroomAmount;
            if ($bookingType === 'diklat') {
                $grandTotal = $discount + $tax + $additionalCharge + $ballroomAmount;
            }

            $additionalRoomIds = array_values(array_diff($selectedRoomIds, [$primaryRoom->id]));

            $booking = Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'guest_id' => $guest->id,
                'room_id' => $primaryRoom->id,
                'additional_room_ids' => $additionalRoomIds,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'num_guests' => $data['num_guests'],
                'num_nights' => $numNights,
                'room_rate' => $roomRate,
                'discount' => $discount,
                'tax' => $tax,
                'additional_charge' => $additionalCharge,
                'ballroom_amount' => $ballroomAmount,
                'grand_total' => $grandTotal,
                'booking_status' => BookingStatus::Confirmed,
                'booking_type' => $bookingType,
                'is_day_use' => $isDayUse,
                'is_early_check_out' => (bool) ($data['is_early_check_out'] ?? false),
                'is_bill_merged' => $isBillMerged,
                'additional_charge_breakdown' => $additionalChargeBreakdown,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $booking->rooms()->sync($selectedRoomIds);

            return $booking;
        });
    }

    private function generateBookingNumber(): string
    {
        $prefix = 'BK-'.date('Y').'-';
        $lastBooking = Booking::where('booking_number', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        if (! $lastBooking) {
            return $prefix.'00001';
        }

        $lastNumber = (int) substr($lastBooking->booking_number, -5);

        return $prefix.str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    private function generateGuestCode(): string
    {
        $prefix = 'GST-'.date('Y').'-';
        $lastGuest = Guest::where('guest_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        $lastNumber = $lastGuest ? (int) substr($lastGuest->guest_code, -5) : 0;

        return $prefix.str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}
