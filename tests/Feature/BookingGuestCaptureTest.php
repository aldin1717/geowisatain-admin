<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Enums\PaymentStatus;
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
            ->assertDontSee('name="guest_id"')
            ->assertDontSee('name="is_day_use"')
            ->assertDontSee('Day Use / Ballroom')
            ->assertDontSee('name="ballroom_amount"')
            ->assertDontSee('Ballroom / Day Use (Rp)');

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
            ->assertDontSee('Identity')
            ->assertDontSee('Add Guest');

        $this->get(route('guests.show', $guest))
            ->assertOk()
            ->assertDontSee('Identity');

        $this->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertDontSee('Identity');

        $this->get(route('bookings.index', ['status' => 'confirmed']))
            ->assertOk()
            ->assertDontSee('New Booking');

        $this->assertDatabaseMissing('guests', ['full_name' => 'Manual Entry']);
        $this->assertFalse(app('router')->has('guests.create'));
        $this->assertFalse(app('router')->has('guests.store'));
    }

    public function test_booking_room_picker_can_filter_rooms_and_hides_unbookable_statuses(): void
    {
        $role = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $roomType = RoomType::create(['name' => 'Superior Twin', 'slug' => 'superior-twin']);

        Room::create([
            'room_number' => '101',
            'room_type_id' => $roomType->id,
            'floor' => '1',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'available',
        ]);
        Room::create([
            'room_number' => '102',
            'room_type_id' => $roomType->id,
            'floor' => '1',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'maintenance',
        ]);
        Room::create([
            'room_number' => '201',
            'room_type_id' => $roomType->id,
            'floor' => '2',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'out_of_service',
        ]);
        Room::create([
            'room_number' => '202',
            'room_type_id' => $roomType->id,
            'floor' => '2',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'cleaning',
        ]);
        Room::create([
            'room_number' => '203',
            'room_type_id' => $roomType->id,
            'floor' => '2',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'reserved',
        ]);
        Room::create([
            'room_number' => '204',
            'room_type_id' => $roomType->id,
            'floor' => '2',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'occupied',
        ]);
        Room::create([
            'room_number' => '205',
            'room_type_id' => $roomType->id,
            'floor' => '2',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'dirty',
        ]);

        $this->actingAs($user)
            ->get(route('bookings.create'))
            ->assertOk()
            ->assertSee('Cari nomor kamar...')
            ->assertSee('Semua Lantai')
            ->assertSee('Lantai 1')
            ->assertSee('Lantai 2')
            ->assertSee('Semua tipe kamar')
            ->assertSee('Room 101 - Superior Twin')
            ->assertSee('Room 102 - Superior Twin')
            ->assertSee('Room 201 - Superior Twin')
            ->assertSee('Room 202 - Superior Twin')
            ->assertSee('Maintenance')
            ->assertSee('Out of Service')
            ->assertSee('Cleaning')
            ->assertSee('Reserved')
            ->assertSee('Occupied')
            ->assertSee('Dirty')
            ->assertSee('disabled', false);
    }

    public function test_receptionist_cannot_book_a_room_in_cleaning_status(): void
    {
        $role = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $roomType = RoomType::create(['name' => 'Superior Twin', 'slug' => 'superior-twin']);
        $room = Room::create([
            'room_number' => '202',
            'room_type_id' => $roomType->id,
            'floor' => '2',
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'cleaning',
        ]);

        $this->actingAs($user)
            ->post(route('bookings.store'), [
                'guest_full_name' => 'Cleaning Room Guest',
                'guest_phone' => '081234567890',
                'guest_email' => 'cleaning-room@example.test',
                'selection_type' => 'room',
                'room_ids' => [$room->id],
                'check_in_date' => now()->addDays(2)->toDateString(),
                'check_out_date' => now()->addDays(3)->toDateString(),
                'num_guests' => 1,
                'booking_type' => 'general',
            ])
            ->assertSessionHasErrors('room_ids');

        $this->assertDatabaseMissing('guests', ['email' => 'cleaning-room@example.test']);
        $this->assertDatabaseMissing('bookings', ['room_id' => $room->id]);
    }

    public function test_new_booking_creates_a_new_guest_without_changing_existing_guest(): void
    {
        $role = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $roomType = RoomType::create(['name' => 'Superior Twin', 'slug' => 'superior-twin']);
        $room = Room::create([
            'room_number' => '301',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'available',
        ]);
        $existingGuest = Guest::create([
            'guest_code' => 'GST-EXISTING',
            'full_name' => 'Previous Guest Name',
            'phone' => '081234567890',
            'email' => 'shared@example.test',
        ]);

        $this->actingAs($user)->post(route('bookings.store'), [
            'guest_full_name' => 'New Booking Guest',
            'guest_phone' => '081234567890',
            'guest_email' => 'shared@example.test',
            'selection_type' => 'room',
            'room_ids' => [$room->id],
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'num_guests' => 1,
            'booking_type' => 'general',
        ])->assertRedirect();

        $newGuest = Guest::where('email', 'shared@example.test')
            ->where('full_name', 'New Booking Guest')
            ->firstOrFail();

        $this->assertNotSame($existingGuest->id, $newGuest->id);
        $this->assertSame('Previous Guest Name', $existingGuest->fresh()->full_name);
        $this->assertDatabaseCount('guests', 2);
        $this->assertDatabaseHas('bookings', [
            'guest_id' => $newGuest->id,
            'room_id' => $room->id,
        ]);
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

    public function test_diklat_booking_has_zero_room_rate_and_total_by_default(): void
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
        $checkInDate = now()->addDays(2)->toDateString();

        $this->actingAs($user)->post(route('bookings.store'), [
            'guest_full_name' => 'Diklat Guest',
            'guest_phone' => '081234567892',
            'guest_email' => 'diklat@example.com',
            'selection_type' => 'room',
            'room_ids' => [$room->id],
            'check_in_date' => $checkInDate,
            'check_out_date' => now()->addDays(4)->toDateString(),
            'num_guests' => 2,
            'booking_type' => 'diklat',
        ])->assertRedirect();

        $booking = Booking::whereHas('guest', fn ($query) => $query->where('email', 'diklat@example.com'))->firstOrFail();
        $this->assertSame(0.0, (float) $booking->room_rate);
        $this->assertSame(0.0, (float) $booking->grand_total);
        $this->assertSame(PaymentStatus::Paid, $booking->payment_status);

        $this->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Diklat');

        $this->get(route('bookings.check-in.receipt', $booking))
            ->assertOk()
            ->assertSee('Diklat');

        $this->get(route('reports.index', [
            'period' => 'week',
            'date' => $checkInDate,
        ]))
            ->assertOk()
            ->assertSee('Booking & Payment Activity')
            ->assertSee($booking->booking_number)
            ->assertSee('Diklat')
            ->assertSee('Diklat Guest')
            ->assertSee('Rp 0');
    }
}
