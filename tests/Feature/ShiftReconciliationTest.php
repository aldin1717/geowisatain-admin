<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_are_required_to_belong_to_an_open_shift_and_close_reconciles_cash(): void
    {
        [$user, $booking] = $this->createBookingAndUser();
        $this->actingAs($user);

        $this->post(route('bookings.payments.store', $booking), [
            'amount' => 100000,
            'payment_method' => PaymentMethod::Cash->value,
        ])->assertRedirect()->assertSessionHas('error', 'Buka shift aktif sebelum mencatat pembayaran.');
        $this->assertDatabaseCount('payments', 0);

        $this->post(route('shifts.open'), [
            'attendant_name' => 'Petugas Pagi',
            'opening_cash' => 200000,
        ])
            ->assertRedirect();
        $shift = Shift::query()->firstOrFail();
        $this->assertSame('Petugas Pagi', $shift->attendant_name);
        $this->get(route('shifts.index'))
            ->assertOk()
            ->assertSee('Petugas yang bertugas: Petugas Pagi');
        $this->get(route('shifts.show', $shift))
            ->assertOk()
            ->assertSee('Petugas yang bertugas: Petugas Pagi');

        $this->post(route('shifts.open'), [
            'attendant_name' => 'Petugas Malam',
            'opening_cash' => 50000,
        ])
            ->assertSessionHasErrors('shift');
        $this->assertDatabaseCount('shifts', 1);

        $this->post(route('bookings.payments.store', $booking), [
            'amount' => 100000,
            'payment_method' => PaymentMethod::Cash->value,
        ])->assertRedirect();
        $this->post(route('bookings.payments.store', $booking), [
            'amount' => 50000,
            'payment_method' => PaymentMethod::Transfer->value,
        ])->assertRedirect();

        $this->assertDatabaseCount('payments', 2);
        $this->assertSame(2, Payment::query()->where('shift_id', $shift->id)->count());

        $this->get(route('shifts.show', $shift))
            ->assertOk()
            ->assertViewHas('expectedCash', 300000.0)
            ->assertViewHas('methodTotals', function ($totals): bool {
                return $totals[PaymentMethod::Cash->value] === 100000.0
                    && $totals[PaymentMethod::Transfer->value] === 50000.0;
            })
            ->assertSee('PAY-');

        $this->post(route('shifts.close', $shift), [
            'closing_cash' => 295000,
            'closing_notes' => 'Selisih Rp 5.000 saat hitung ulang.',
        ])->assertRedirect(route('shifts.show', $shift));

        $shift->refresh();
        $this->assertNotNull($shift->closed_at);
        $this->assertSame(300000.0, (float) $shift->expected_cash);
        $this->assertSame(-5000.0, (float) $shift->cash_variance);
        $this->assertSame('Selisih Rp 5.000 saat hitung ulang.', $shift->closing_notes);

        $this->post(route('bookings.payments.store', $booking), [
            'amount' => 100000,
            'payment_method' => PaymentMethod::Cash->value,
        ])->assertSessionHas('error', 'Buka shift aktif sebelum mencatat pembayaran.');
        $this->assertDatabaseCount('payments', 2);
    }

    /**
     * @return array{User, Booking}
     */
    private function createBookingAndUser(): array
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $guest = Guest::create(['guest_code' => 'G-SHIFT-001', 'full_name' => 'Shift Guest']);
        $roomType = RoomType::create(['name' => 'Shift Room', 'slug' => 'shift-room']);
        $room = Room::create([
            'room_number' => 'SH-101',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 250000,
        ]);
        $booking = Booking::create([
            'booking_number' => 'BK-SHIFT-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => today(),
            'check_out_date' => today()->addDays(2),
            'num_guests' => 1,
            'num_nights' => 2,
            'room_rate' => 250000,
            'grand_total' => 500000,
            'booking_status' => 'confirmed',
            'payment_status' => 'unpaid',
        ]);
        $booking->rooms()->sync([$room->id]);

        return [$user, $booking];
    }
}
