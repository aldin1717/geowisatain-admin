<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\CheckInService;
use App\Services\RoomAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_is_available_from_the_previous_bookings_checkout_date(): void
    {
        $room = $this->createRoom();
        $guest = Guest::create([
            'guest_code' => 'G-AVAIL-001',
            'full_name' => 'Previous Guest',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-AVAIL-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => '2026-10-07',
            'check_out_date' => '2026-10-10',
            'num_guests' => 1,
            'num_nights' => 3,
            'room_rate' => 300000,
            'grand_total' => 900000,
            'booking_status' => BookingStatus::CheckedIn,
        ]);
        $booking->rooms()->sync([$room->id]);

        $availability = app(RoomAvailabilityService::class);

        $this->assertTrue($availability->isAvailable($room->id, '2026-10-10', '2026-10-12'));
        $this->assertFalse($availability->isAvailable($room->id, '2026-10-09', '2026-10-11'));
        $this->assertFalse($availability->isAvailable($room->id, '2026-10-08', '2026-10-09'));
    }

    public function test_confirmed_booking_can_check_in_early_when_room_is_available(): void
    {
        $room = $this->createRoom();
        $room->update(['status' => 'available']);
        $guest = Guest::create([
            'guest_code' => 'G-EARLY-001',
            'full_name' => 'Early Check-in Guest',
        ]);
        $booking = Booking::create([
            'booking_number' => 'BK-EARLY-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => today()->addDays(2)->toDateString(),
            'check_out_date' => today()->addDays(4)->toDateString(),
            'num_guests' => 1,
            'num_nights' => 2,
            'room_rate' => 300000,
            'grand_total' => 600000,
            'booking_status' => BookingStatus::Confirmed,
        ]);
        $booking->rooms()->sync([$room->id]);

        app(CheckInService::class)->process($booking);

        $this->assertSame(BookingStatus::CheckedIn, $booking->fresh()->booking_status);
        $this->assertSame('occupied', $room->fresh()->status->value);
    }

    private function createRoom(): Room
    {
        $roomType = RoomType::create([
            'name' => 'Availability Test Room',
            'slug' => 'availability-test-room',
        ]);

        return Room::create([
            'room_number' => 'AV-101',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'occupied',
        ]);
    }
}
