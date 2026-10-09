<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_calendar_marks_booking_nights_and_releases_room_on_checkout_date(): void
    {
        $role = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $guest = Guest::create([
            'guest_code' => 'G-CALENDAR-001',
            'full_name' => 'Calendar Guest',
        ]);
        $roomType = RoomType::create(['name' => 'Calendar Room', 'slug' => 'calendar-room']);
        $room = Room::create([
            'room_number' => 'CAL-101',
            'room_type_id' => $roomType->id,
            'floor' => '1',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'occupied',
        ]);
        Room::create([
            'room_number' => 'CAL-201',
            'room_type_id' => $roomType->id,
            'floor' => '2',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'available',
        ]);
        $booking = Booking::create([
            'booking_number' => 'BK-CALENDAR-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => '2026-10-12',
            'check_out_date' => '2026-10-14',
            'num_guests' => 1,
            'num_nights' => 2,
            'room_rate' => 300000,
            'grand_total' => 600000,
            'booking_status' => BookingStatus::Confirmed,
        ]);
        $booking->rooms()->sync([$room->id]);

        $response = $this->actingAs($user)
            ->get(route('room-calendar.index', ['month' => '2026-10']))
            ->assertOk();

        $response
            ->assertSee('Room Calendar')
            ->assertSee('Calendar Guest')
            ->assertSee(route('bookings.show', $booking))
            ->assertSee('Semua lantai')
            ->assertSee('Lantai 1')
            ->assertViewHas('calendar', function ($calendar) use ($room, $booking): bool {
                $row = $calendar->first(fn (array $item) => $item['room']->id === $room->id);
                $cellsByDay = collect($row['cells'])->keyBy(fn (array $cell) => $cell['date']->format('Y-m-d'));

                return $cellsByDay['2026-10-12']['booking']?->id === $booking->id
                    && $cellsByDay['2026-10-13']['booking']?->id === $booking->id
                    && $cellsByDay['2026-10-14']['booking'] === null
                    && $cellsByDay['2026-10-14']['status'] === 'available';
            });

        $this->get(route('room-calendar.index', ['month' => '2026-10', 'floor' => '1']))
            ->assertOk()
            ->assertSee('CAL-101')
            ->assertDontSee('CAL-201')
            ->assertSee('value="1" selected', false);
    }
}
