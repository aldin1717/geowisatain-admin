<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Enums\BookingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_live_hotel_statistics_and_recent_bookings(): void
    {
        $role = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $roomType = RoomType::create([
            'name' => 'Standard',
            'slug' => 'standard',
            'base_price' => 500000,
            'capacity' => 2,
        ]);
        $room = Room::create([
            'room_number' => '101',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 500000,
        ]);
        $guest = Guest::create([
            'guest_code' => 'GST-001',
            'full_name' => 'Dashboard Guest',
            'identity_number' => '1234567890',
            'identity_type' => 'ktp',
        ]);
        Booking::create([
            'booking_number' => 'BK-TODAY',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => today(),
            'check_out_date' => today()->addDay(),
            'num_guests' => 1,
            'num_nights' => 1,
            'room_rate' => 500000,
            'grand_total' => 500000,
            'booking_status' => 'confirmed',
        ]);
        $departureRoom = Room::create([
            'room_number' => '102',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 500000,
        ]);
        Booking::create([
            'booking_number' => 'BK-DEPARTURE-TODAY',
            'guest_id' => $guest->id,
            'room_id' => $departureRoom->id,
            'check_in_date' => today()->subDay(),
            'check_out_date' => today(),
            'num_guests' => 1,
            'num_nights' => 1,
            'room_rate' => 500000,
            'grand_total' => 500000,
            'booking_status' => BookingStatus::CheckedIn,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('BK-TODAY')
            ->assertSee('Dashboard Guest')
            ->assertViewHas('todayArrivals', 1)
            ->assertViewHas('todayDepartures', 1)
            ->assertSee(route('bookings.index', [
                'status' => 'confirmed',
                'check_in_date' => today()->toDateString(),
            ]))
            ->assertSee(route('bookings.index', [
                'status' => 'checked_in',
                'check_out_date' => today()->toDateString(),
            ]));

        $this->get(route('bookings.index', [
            'status' => 'confirmed',
            'check_in_date' => today()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('BK-TODAY')
            ->assertDontSee('BK-DEPARTURE-TODAY');

        $this->get(route('bookings.index', [
            'status' => 'checked_in',
            'check_out_date' => today()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('BK-DEPARTURE-TODAY')
            ->assertDontSee('BK-TODAY');
    }
}
