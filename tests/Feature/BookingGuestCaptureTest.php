<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingGuestCaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_captures_guest_details_and_guest_page_is_read_only(): void
    {
        $role = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $roomType = RoomType::create(['name' => 'Deluxe', 'slug' => 'deluxe']);
        $room = Room::create([
            'room_number' => '101',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 500000,
            'status' => 'available',
        ]);

        $this->actingAs($user)
            ->get(route('bookings.create'))
            ->assertOk()
            ->assertSee('guest_full_name')
            ->assertDontSee('guest_identity_type')
            ->assertSee('guest_phone')
            ->assertSee('guest_email')
            ->assertSee('guest_address')
            ->assertDontSee('name="guest_id"');

        $response = $this->post(route('bookings.store'), [
            'guest_full_name' => 'Dewi Lestari',
            'guest_phone' => '081234567890',
            'guest_email' => 'dewi@example.com',
            'guest_address' => 'Bandung',
            'room_id' => $room->id,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'num_guests' => 2,
            'additional_charge_breakdown' => [
                ['name' => 'Bantal tambahan', 'amount' => 25000],
                ['name' => 'Laundry', 'amount' => 50000],
            ],
        ]);

        $response->assertRedirect();

        $guest = Guest::where('email', 'dewi@example.com')->firstOrFail();
        $booking = Booking::where('guest_id', $guest->id)->firstOrFail();
        $this->assertSame('Dewi Lestari', $guest->full_name);
        $this->assertNull($guest->identity_type);
        $this->assertSame('Bandung', $guest->address);
        $this->assertSame($room->id, $booking->room_id);
        $this->assertSame(75000.0, (float) $booking->additional_charge);
        $this->assertSame([
            ['name' => 'Bantal tambahan', 'amount' => 25000],
            ['name' => 'Laundry', 'amount' => 50000],
        ], $booking->additional_charge_breakdown);

        $this->get(route('guests.index'))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertDontSee('Add Guest');

        $this->get(route('bookings.index', ['status' => 'confirmed']))
            ->assertOk()
            ->assertDontSee('New Booking');

        $this->assertDatabaseMissing('guests', ['full_name' => 'Manual Entry']);
        $this->assertFalse(app('router')->has('guests.create'));
        $this->assertFalse(app('router')->has('guests.store'));
    }

    public function test_booking_can_select_multiple_master_rooms_and_ballrooms(): void
    {
        $role = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $roomType = RoomType::create(['name' => 'Deluxe', 'slug' => 'deluxe']);
        $ballroomType = RoomType::create([
            'name' => 'Main Ballroom',
            'slug' => 'main-ballroom',
            'category' => 'ballroom',
        ]);
        $room = Room::create([
            'room_number' => '101',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 500000,
            'status' => 'available',
        ]);
        $secondRoom = Room::create([
            'room_number' => '102',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'available',
        ]);
        $ballroom = Room::create([
            'room_number' => 'B1',
            'room_type_id' => $ballroomType->id,
            'capacity' => 100,
            'price_per_night' => 0,
            'status' => 'available',
        ]);

        $this->actingAs($user)
            ->get(route('bookings.create'))
            ->assertOk()
            ->assertSee('Ballroom B1');

        $response = $this->post(route('bookings.store'), [
            'guest_full_name' => 'Combined Guest',
            'guest_phone' => '081234567891',
            'guest_email' => 'combined@example.com',
            'selection_type' => 'both',
            'room_ids' => [$room->id, $secondRoom->id, $ballroom->id],
            'check_in_date' => now()->addDays(6)->toDateString(),
            'check_out_date' => now()->addDays(8)->toDateString(),
            'num_guests' => 3,
            'ballroom_amount' => 50000,
        ]);

        $response->assertRedirect();

        $booking = Booking::whereHas('guest', fn ($query) => $query->where('email', 'combined@example.com'))->firstOrFail();
        $this->assertSame($room->id, $booking->room_id);
        $this->assertCount(2, $booking->additional_room_ids);
        $this->assertSame(800000.0, (float) $booking->room_rate);
        $this->assertSame(1650000.0, (float) $booking->grand_total);
        $this->assertCount(3, $booking->rooms);
        $this->assertTrue($booking->rooms->contains('id', $ballroom->id));
    }
}
