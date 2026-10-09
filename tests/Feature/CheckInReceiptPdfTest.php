<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
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
        Payment::create([
            'payment_number' => 'PAY-CHECKIN-001',
            'booking_id' => $booking->id,
            'payment_date' => now(),
            'amount' => 500000,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($user)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('id="checkInForm"', false)
            ->assertSee(':form="confirmationType', false);

        $this->post(route('bookings.check-in', $booking))
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('success', 'Check-in processed successfully.');

        $this->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('id="checkOutForm"', false)
            ->assertSee('id="earlyCheckOutForm"', false)
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

    public function test_booking_with_unpaid_or_partial_balance_cannot_check_in(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $guest = Guest::create(['guest_code' => 'GST-002', 'full_name' => 'Unpaid Guest']);
        $roomType = RoomType::create(['name' => 'Standard', 'slug' => 'standard']);
        $room = Room::create([
            'room_number' => '102',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 500000,
        ]);
        $booking = Booking::create([
            'booking_number' => 'BK-002',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'num_guests' => 1,
            'num_nights' => 1,
            'room_rate' => 500000,
            'grand_total' => 500000,
            'booking_status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $this->actingAs($user)
            ->from(route('bookings.show', $booking))
            ->post(route('bookings.check-in', $booking))
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('error', 'Cannot check-in before payment is completed.');

        Payment::create([
            'payment_number' => 'PAY-CHECKIN-002',
            'booking_id' => $booking->id,
            'payment_date' => now(),
            'amount' => 250000,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Paid,
        ]);
        $booking->update(['payment_status' => PaymentStatus::Partial]);

        $this->from(route('bookings.show', $booking))
            ->post(route('bookings.check-in', $booking))
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('error', 'Cannot check-in before payment is completed.');

        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->booking_status);
        $this->assertSame('available', $room->fresh()->status->value);
    }
}
