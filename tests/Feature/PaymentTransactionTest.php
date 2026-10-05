<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_record_a_booking_payment(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Administrator',
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $guest = Guest::create([
            'guest_code' => 'G-001',
            'full_name' => 'John Doe',
            'identity_number' => '1234567890123',
            'identity_type' => 'ktp',
            'gender' => 'male',
            'phone' => '081234567890',
            'email' => 'john@example.com',
            'address' => 'Bandung',
            'city' => 'Bandung',
            'country' => 'Indonesia',
        ]);

        $roomType = RoomType::create([
            'name' => 'Deluxe',
            'slug' => 'deluxe',
            'description' => 'Deluxe room',
            'base_price' => 500000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        $room = Room::create([
            'room_number' => '101',
            'room_type_id' => $roomType->id,
            'floor' => 1,
            'capacity' => 2,
            'price_per_night' => 500000,
            'status' => 'available',
            'description' => 'Room 101',
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-2026-00001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => '2026-10-10',
            'check_out_date' => '2026-10-12',
            'num_guests' => 2,
            'num_nights' => 2,
            'room_rate' => 500000,
            'discount' => 0,
            'tax' => 0,
            'additional_charge' => 0,
            'grand_total' => 1000000,
            'booking_status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Unpaid,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('bookings.payments.store', $booking), [
                'amount' => 1000000,
                'payment_method' => PaymentMethod::Transfer->value,
                'notes' => 'Full payment',
            ])
            ->assertRedirect();

        $booking->refresh();

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => '1000000.00',
            'payment_method' => PaymentMethod::Transfer->value,
        ]);

        $this->assertSame(PaymentStatus::Paid->value, $booking->payment_status->value);

        $this->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Payment History')
            ->assertSee('PAY-');

        $filters = ['period' => 'month', 'date' => now()->toDateString()];

        $this->get(route('reports.index', $filters))
            ->assertOk()
            ->assertSee('Payment Reports')
            ->assertSee('Rp 1.000.000')
            ->assertSee('PAY-');

        $this->get(route('reports.index', ['period' => 'week', 'date' => now()->toDateString()]))
            ->assertOk()
            ->assertSee('Rp 1.000.000');

        $exportResponse = $this->get(route('reports.export', $filters));
        $exportResponse
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition');

        ob_start();
        $exportResponse->sendContent();
        $csvContent = ob_get_clean();

        $this->assertStringContainsString('PAY-', $csvContent);
        $this->assertStringContainsString('John Doe', $csvContent);
    }

    public function test_one_payer_can_pay_for_multiple_bookings_with_one_payment(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrator']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $payer = Guest::create([
            'guest_code' => 'G-101',
            'full_name' => 'Payer Guest',
            'identity_number' => null,
            'identity_type' => null,
            'gender' => 'male',
            'phone' => '081111111111',
            'email' => 'payer@example.com',
            'address' => 'Bandung',
            'city' => 'Bandung',
            'country' => 'Indonesia',
        ]);
        $secondGuest = Guest::create([
            'guest_code' => 'G-102',
            'full_name' => 'Second Guest',
            'identity_number' => null,
            'identity_type' => null,
            'gender' => 'female',
            'phone' => '082222222222',
            'email' => 'second@example.com',
            'address' => 'Jakarta',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
        ]);
        $roomType = RoomType::create(['name' => 'Standard', 'slug' => 'standard']);
        $firstRoom = Room::create([
            'room_number' => '201',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 500000,
            'status' => 'available',
        ]);
        $secondRoom = Room::create([
            'room_number' => '202',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 500000,
            'status' => 'available',
        ]);

        $firstBooking = Booking::create([
            'booking_number' => 'BK-2026-00101',
            'guest_id' => $payer->id,
            'room_id' => $firstRoom->id,
            'check_in_date' => '2026-10-10',
            'check_out_date' => '2026-10-12',
            'num_guests' => 2,
            'num_nights' => 2,
            'room_rate' => 500000,
            'grand_total' => 1000000,
            'booking_status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Unpaid,
            'created_by' => $user->id,
        ]);
        $secondBooking = Booking::create([
            'booking_number' => 'BK-2026-00102',
            'guest_id' => $secondGuest->id,
            'room_id' => $secondRoom->id,
            'check_in_date' => '2026-10-10',
            'check_out_date' => '2026-10-12',
            'num_guests' => 2,
            'num_nights' => 2,
            'room_rate' => 250000,
            'grand_total' => 500000,
            'booking_status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Unpaid,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('billing-groups.store'), [
                'booking_ids' => [$firstBooking->id, $secondBooking->id],
                'payer_guest_id' => $payer->id,
            ])
            ->assertRedirect();

        $firstBooking->refresh();
        $secondBooking->refresh();
        $billingGroup = $firstBooking->billingGroup;
        $this->assertNotNull($billingGroup);
        $this->assertSame($billingGroup->id, $secondBooking->billing_group_id);
        $this->assertSame($payer->id, $billingGroup->payer_guest_id);

        $this->post(route('billing-groups.payments.store', $billingGroup), [
            'amount' => 1500000,
            'payment_method' => PaymentMethod::Transfer->value,
            'notes' => 'Paid by group organizer',
        ])->assertRedirect();

        $firstBooking->refresh();
        $secondBooking->refresh();
        $this->assertSame(PaymentStatus::Paid, $firstBooking->payment_status);
        $this->assertSame(PaymentStatus::Paid, $secondBooking->payment_status);
        $this->assertSame(PaymentStatus::Paid->value, $billingGroup->fresh()->payment_status);
        $this->assertDatabaseHas('payments', [
            'billing_group_id' => $billingGroup->id,
            'booking_id' => null,
            'amount' => '1500000.00',
        ]);
        $this->assertDatabaseHas('booking_payment_allocations', [
            'booking_id' => $firstBooking->id,
            'amount' => '1000000.00',
        ]);
        $this->assertDatabaseHas('booking_payment_allocations', [
            'booking_id' => $secondBooking->id,
            'amount' => '500000.00',
        ]);
    }
}
