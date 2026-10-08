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

class CheckInReceiptPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_download_check_in_receipt_as_pdf(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $guest = Guest::create([
            'guest_code' => 'GST-001',
            'full_name' => 'Test Guest',
            'identity_number' => '1234567890',
            'identity_type' => 'ktp',
        ]);
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
        $booking = Booking::create([
            'booking_number' => 'BK-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'num_guests' => 1,
            'num_nights' => 1,
            'room_rate' => 500000,
            'grand_total' => 500000,
            'booking_status' => 'confirmed',
        ]);

        $this->actingAs($user)
            ->post(route('bookings.check-in', $booking))
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('success', 'Check-in processed successfully.');

        $this->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Check-in processed successfully.')
            ->assertSee(route('bookings.check-in.receipt', $booking))
            ->assertSee('target="_blank"', false)
            ->assertDontSee(route('bookings.check-in.receipt.download', $booking));

        $this->get(route('bookings.check-in.receipt', $booking))
            ->assertOk()
            ->assertSee(route('bookings.check-in.receipt.download', $booking));

        $response = $this->actingAs($user)
            ->get(route('bookings.check-in.receipt.download', $booking));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename=check-in-BK-001.pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
