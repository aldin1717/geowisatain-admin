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
}
