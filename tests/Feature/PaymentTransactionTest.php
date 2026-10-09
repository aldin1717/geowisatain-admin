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
use App\Services\CheckOutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_summary_cards_use_day_week_and_month_ranges(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $guest = Guest::create(['guest_code' => 'G-REPORT-001', 'full_name' => 'Report Guest']);
        $roomType = RoomType::create(['name' => 'Report Room', 'slug' => 'report-room']);
        $room = Room::create([
            'room_number' => 'R-101',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 100000,
        ]);
        $booking = Booking::create([
            'booking_number' => 'BK-REPORT-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => '2026-10-08',
            'check_out_date' => today()->toDateString(),
            'num_guests' => 1,
            'num_nights' => 1,
            'room_rate' => 100000,
            'grand_total' => 100000,
        ]);

        foreach ([
            ['RPT-001', '2026-10-08 09:00:00', 100000],
            ['RPT-002', '2026-10-07 09:00:00', 200000],
            ['RPT-003', '2026-10-12 09:00:00', 400000],
            ['RPT-004', '2026-09-30 09:00:00', 800000],
        ] as [$number, $date, $amount]) {
            Payment::create([
                'payment_number' => $number,
                'booking_id' => $booking->id,
                'payment_date' => $date,
                'amount' => $amount,
                'payment_method' => PaymentMethod::Cash,
                'payment_status' => PaymentStatus::Paid,
            ]);
        }

        $this->actingAs($user)
            ->get(route('reports.index', ['period' => 'week', 'date' => '2026-10-08']))
            ->assertOk()
            ->assertViewHas('summary', function (array $summary): bool {
                return (float) $summary['day'] === 100000.0
                    && (float) $summary['week'] === 300000.0
                    && (float) $summary['month'] === 700000.0;
            });
    }

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
            ->post(route('shifts.open'), ['attendant_name' => 'Petugas Pagi', 'opening_cash' => 0])
            ->assertRedirect();

        $this->post(route('bookings.payments.store', $booking), [
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
            ->assertSee('PAY-')
            ->assertSee(route('reports.export.pdf', $filters));

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
        $this->assertStringContainsString('Tipe Booking', $csvContent);

        $pdfResponse = $this->get(route('reports.export.pdf', $filters));
        $pdfResponse
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition');
        $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
    }

    public function test_checked_in_diklat_booking_with_zero_total_can_check_out_without_payment(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $guest = Guest::create([
            'guest_code' => 'G-DIKLAT-001',
            'full_name' => 'Diklat Guest',
            'phone' => '081234567890',
            'email' => 'diklat@example.com',
        ]);
        $roomType = RoomType::create(['name' => 'Superior', 'slug' => 'superior']);
        $room = Room::create([
            'room_number' => '301',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'occupied',
        ]);
        $booking = Booking::create([
            'booking_number' => 'BK-2026-DIKLAT-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => '2026-10-07',
            'check_out_date' => today()->toDateString(),
            'num_guests' => 1,
            'num_nights' => 2,
            'room_rate' => 0,
            'grand_total' => 0,
            'booking_status' => BookingStatus::CheckedIn,
            'payment_status' => PaymentStatus::Unpaid,
            'booking_type' => 'diklat',
            'created_by' => $user->id,
        ]);
        $booking->rooms()->sync([$room->id]);

        $this->actingAs($user)
            ->post(route('bookings.check-out', $booking))
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('success', 'Check-out processed successfully.');

        $this->assertSame(BookingStatus::CheckedOut, $booking->fresh()->booking_status);
        $this->assertSame(PaymentStatus::Paid, $booking->fresh()->payment_status);
        $this->assertSame('cleaning', $room->fresh()->status->value);
    }

    public function test_checked_in_booking_can_check_out_early_without_changing_booking_total(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $guest = Guest::create([
            'guest_code' => 'G-EARLY-001',
            'full_name' => 'Early Checkout Guest',
        ]);
        $roomType = RoomType::create(['name' => 'Early Checkout', 'slug' => 'early-checkout']);
        $room = Room::create([
            'room_number' => '303',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 300000,
            'status' => 'occupied',
        ]);
        $booking = Booking::create([
            'booking_number' => 'BK-EARLY-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => today()->subDay()->toDateString(),
            'check_out_date' => today()->addDays(2)->toDateString(),
            'num_guests' => 1,
            'num_nights' => 3,
            'room_rate' => 300000,
            'grand_total' => 900000,
            'booking_status' => BookingStatus::CheckedIn,
            'payment_status' => PaymentStatus::Paid,
        ]);
        $booking->rooms()->sync([$room->id]);
        Payment::create([
            'payment_number' => 'PAY-EARLY-001',
            'booking_id' => $booking->id,
            'payment_date' => now(),
            'amount' => 900000,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($user)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('id="checkOutForm"', false)
            ->assertSee('id="earlyCheckOutForm"', false)
            ->assertSee(':form="confirmationType', false)
            ->assertSee(route('bookings.check-out', $booking))
            ->assertSee(route('bookings.early-check-out', $booking))
            ->assertSee('Early Check-out')
            ->assertSee('early_check_out_reason')
            ->assertSee('Total booking tetap sama dan tidak ada refund otomatis.');

        $this->post(route('bookings.early-check-out', $booking))
            ->assertSessionHasErrors('early_check_out_reason');
        $this->assertSame(BookingStatus::CheckedIn, $booking->fresh()->booking_status);

        $this->post(route('bookings.early-check-out', $booking), [
            'early_check_out_reason' => 'Guest needs to leave earlier.',
        ])
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('success', 'Early check-out processed successfully.');

        $checkedOutBooking = $booking->fresh();
        $this->assertSame(BookingStatus::CheckedOut, $checkedOutBooking->booking_status);
        $this->assertTrue($checkedOutBooking->is_early_check_out);
        $this->assertStringContainsString('Early check-out reason: Guest needs to leave earlier.', $checkedOutBooking->notes);
        $this->assertSame(900000.0, (float) $checkedOutBooking->grand_total);
        $this->assertSame(today()->addDays(2)->toDateString(), $checkedOutBooking->check_out_date->toDateString());
        $this->assertSame('cleaning', $room->fresh()->status->value);
    }

    public function test_checked_in_booking_with_outstanding_balance_cannot_check_out(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $guest = Guest::create([
            'guest_code' => 'G-UNPAID-001',
            'full_name' => 'Unpaid Guest',
            'phone' => '081234567891',
            'email' => 'unpaid@example.com',
        ]);
        $roomType = RoomType::create(['name' => 'Deluxe', 'slug' => 'deluxe']);
        $room = Room::create([
            'room_number' => '302',
            'room_type_id' => $roomType->id,
            'capacity' => 2,
            'price_per_night' => 400000,
            'status' => 'occupied',
        ]);
        $booking = Booking::create([
            'booking_number' => 'BK-2026-UNPAID-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in_date' => '2026-10-07',
            'check_out_date' => today()->toDateString(),
            'num_guests' => 1,
            'num_nights' => 2,
            'room_rate' => 400000,
            'grand_total' => 800000,
            'booking_status' => BookingStatus::CheckedIn,
            'payment_status' => PaymentStatus::Unpaid,
            'created_by' => $user->id,
        ]);
        $booking->rooms()->sync([$room->id]);

        $this->actingAs($user);
        try {
            app(CheckOutService::class)->process($booking);
            $this->fail('Check-out must be rejected when the booking has an outstanding balance.');
        } catch (\Exception $exception) {
            $this->assertSame('Cannot check-out before payment is completed.', $exception->getMessage());
        }

        $this->assertSame(BookingStatus::CheckedIn, $booking->fresh()->booking_status);
        $this->assertSame('occupied', $room->fresh()->status->value);
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

        $this->post(route('shifts.open'), ['attendant_name' => 'Petugas Sore', 'opening_cash' => 100000])
            ->assertRedirect();

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

        $filters = ['period' => 'month', 'date' => now()->toDateString()];
        $this->get(route('reports.index', $filters))
            ->assertOk()
            ->assertViewHas('paymentReportDetails', function (array $details) use ($billingGroup): bool {
                $payment = $billingGroup->payments()->firstOrFail();

                return $details[$payment->id]['booking_type'] === 'Umum';
            })
            ->assertSee('201, 202')
            ->assertSee('BK-2026-00101: Confirmed · Paid')
            ->assertSee('BK-2026-00102: Confirmed · Paid');

        $exportResponse = $this->get(route('reports.export', $filters))->assertOk();
        ob_start();
        $exportResponse->sendContent();
        $csvContent = ob_get_clean();

        $this->assertStringContainsString('201, 202', $csvContent);
        $this->assertStringContainsString(';Umum;201, 202;', $csvContent);
        $this->assertStringNotContainsString(';Tagihan Gabungan;', $csvContent);
        $this->assertStringContainsString('BK-2026-00101: Confirmed · Paid', $csvContent);
    }
}
