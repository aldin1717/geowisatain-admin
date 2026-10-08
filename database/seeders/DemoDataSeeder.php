<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\BillingGroup;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'receptionist@hotel.test')->firstOrFail();
        $warehouseUser = User::where('email', 'warehouse@hotel.test')->firstOrFail();

        $roomTypes = collect([
            ['name' => 'Demo Standard', 'slug' => 'demo-standard', 'category' => 'room', 'base_price' => 350000, 'capacity' => 2],
            ['name' => 'Demo Deluxe', 'slug' => 'demo-deluxe', 'category' => 'room', 'base_price' => 550000, 'capacity' => 2],
            ['name' => 'Demo Suite', 'slug' => 'demo-suite', 'category' => 'room', 'base_price' => 750000, 'capacity' => 3],
            ['name' => 'Demo Family', 'slug' => 'demo-family', 'category' => 'room', 'base_price' => 900000, 'capacity' => 4],
            ['name' => 'Demo Ballroom', 'slug' => 'demo-ballroom', 'category' => 'ballroom', 'base_price' => 0, 'capacity' => 100],
        ])->mapWithKeys(function (array $attributes) {
            $slug = $attributes['slug'];
            $roomType = RoomType::updateOrCreate(['slug' => $slug], $attributes + [
                'description' => 'Data contoh untuk mencoba fitur aplikasi.',
                'is_active' => true,
            ]);

            return [$slug => $roomType];
        });

        $amenities = collect([
            ['name' => 'Demo Wi-Fi', 'icon' => 'wifi'],
            ['name' => 'Demo TV', 'icon' => 'tv'],
            ['name' => 'Demo AC', 'icon' => 'snowflake'],
            ['name' => 'Demo Breakfast', 'icon' => 'utensils'],
            ['name' => 'Demo Meeting Setup', 'icon' => 'users'],
        ])->mapWithKeys(function (array $attributes) {
            $amenity = Amenity::updateOrCreate(['name' => $attributes['name']], $attributes);

            return [$attributes['name'] => $amenity];
        });

        $roomDefinitions = [
            ['room_number' => 'D-101', 'type' => 'demo-standard', 'floor' => '1', 'capacity' => 2, 'price_per_night' => 350000],
            ['room_number' => 'D-102', 'type' => 'demo-deluxe', 'floor' => '1', 'capacity' => 2, 'price_per_night' => 550000],
            ['room_number' => 'D-201', 'type' => 'demo-suite', 'floor' => '2', 'capacity' => 3, 'price_per_night' => 750000],
            ['room_number' => 'D-202', 'type' => 'demo-family', 'floor' => '2', 'capacity' => 4, 'price_per_night' => 900000],
            ['room_number' => 'D-B01', 'type' => 'demo-ballroom', 'floor' => '1', 'capacity' => 100, 'price_per_night' => 0],
        ];

        $rooms = collect($roomDefinitions)->mapWithKeys(function (array $attributes, int $index) use ($roomTypes, $amenities) {
            $room = Room::updateOrCreate(
                ['room_number' => $attributes['room_number']],
                [
                    'room_type_id' => $roomTypes[$attributes['type']]->id,
                    'floor' => $attributes['floor'],
                    'capacity' => $attributes['capacity'],
                    'price_per_night' => $attributes['price_per_night'],
                    'status' => 'available',
                    'description' => 'Kamar demo untuk mencoba fitur aplikasi.',
                    'is_active' => true,
                ]
            );
            $room->amenities()->sync($amenities->values()->slice(0, $index + 1)->pluck('id'));

            return [$attributes['room_number'] => $room];
        });

        $guestDefinitions = [
            ['guest_code' => 'DEMO-GST-001', 'full_name' => 'Demo General Guest'],
            ['guest_code' => 'DEMO-GST-002', 'full_name' => 'Demo Corporate Guest'],
            ['guest_code' => 'DEMO-GST-003', 'full_name' => 'Demo ESDM Guest'],
            ['guest_code' => 'DEMO-GST-004', 'full_name' => 'Demo Travel Guest'],
            ['guest_code' => 'DEMO-GST-005', 'full_name' => 'Demo Diklat Guest'],
        ];

        $guests = collect($guestDefinitions)->mapWithKeys(function (array $attributes, int $index) {
            $guest = Guest::updateOrCreate(
                ['guest_code' => $attributes['guest_code']],
                [
                    'full_name' => $attributes['full_name'],
                    'identity_number' => null,
                    'identity_type' => null,
                    'gender' => null,
                    'phone' => '081234560'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'email' => 'demo.guest'.($index + 1).'@hotel.test',
                    'address' => 'Alamat contoh '.($index + 1),
                    'city' => 'Bandung',
                    'country' => 'Indonesia',
                    'notes' => 'Data demo.',
                ]
            );

            return [$attributes['guest_code'] => $guest];
        });

        $today = now()->startOfDay();
        $bookingDefinitions = [
            [
                'booking_number' => 'DEMO-BK-001',
                'guest' => 'DEMO-GST-001',
                'room' => 'D-101',
                'start' => $today->copy()->subDays(4),
                'nights' => 2,
                'type' => 'general',
                'status' => 'checked_out',
                'payment_status' => 'paid',
            ],
            [
                'booking_number' => 'DEMO-BK-002',
                'guest' => 'DEMO-GST-002',
                'room' => 'D-102',
                'start' => $today->copy()->subDay(),
                'nights' => 2,
                'type' => 'corporate',
                'status' => 'checked_in',
                'payment_status' => 'partial',
            ],
            [
                'booking_number' => 'DEMO-BK-003',
                'guest' => 'DEMO-GST-003',
                'room' => 'D-201',
                'start' => $today->copy()->addDays(3),
                'nights' => 2,
                'type' => 'esdm',
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ],
            [
                'booking_number' => 'DEMO-BK-004',
                'guest' => 'DEMO-GST-004',
                'room' => 'D-202',
                'start' => $today->copy()->addDays(6),
                'nights' => 2,
                'type' => 'travel_agent',
                'status' => 'confirmed',
                'payment_status' => 'partial',
            ],
            [
                'booking_number' => 'DEMO-BK-005',
                'guest' => 'DEMO-GST-005',
                'room' => 'D-B01',
                'start' => $today->copy()->addDays(9),
                'nights' => 1,
                'type' => 'diklat',
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ],
        ];

        $bookings = collect($bookingDefinitions)->mapWithKeys(function (array $attributes) use ($guests, $rooms, $user) {
            $room = $rooms[$attributes['room']];
            $roomRate = $attributes['type'] === 'diklat' ? 0 : (float) $room->price_per_night;
            $checkOut = $attributes['start']->copy()->addDays($attributes['nights']);
            $grandTotal = $roomRate * $attributes['nights'];

            $booking = Booking::updateOrCreate(
                ['booking_number' => $attributes['booking_number']],
                [
                    'guest_id' => $guests[$attributes['guest']]->id,
                    'room_id' => $room->id,
                    'additional_room_ids' => [],
                    'check_in_date' => $attributes['start']->toDateString(),
                    'check_out_date' => $checkOut->toDateString(),
                    'num_guests' => 2,
                    'num_nights' => $attributes['nights'],
                    'room_rate' => $roomRate,
                    'discount' => 0,
                    'tax' => 0,
                    'additional_charge' => 0,
                    'ballroom_amount' => 0,
                    'grand_total' => $grandTotal,
                    'booking_status' => $attributes['status'],
                    'payment_status' => $attributes['payment_status'],
                    'booking_type' => $attributes['type'],
                    'is_day_use' => $attributes['type'] === 'diklat' && $room->roomType->category === 'ballroom',
                    'is_early_check_out' => false,
                    'is_bill_merged' => in_array($attributes['booking_number'], ['DEMO-BK-003', 'DEMO-BK-004'], true),
                    'additional_charge_breakdown' => [],
                    'actual_check_in' => $attributes['status'] === 'checked_in' || $attributes['status'] === 'checked_out'
                        ? $attributes['start']->copy()->setTime(14, 0)
                        : null,
                    'actual_check_out' => $attributes['status'] === 'checked_out'
                        ? $checkOut->copy()->setTime(12, 0)
                        : null,
                    'created_by' => $user->id,
                ]
            );
            $booking->rooms()->sync([$room->id]);

            return [$attributes['booking_number'] => $booking];
        });

        $billingGroup = BillingGroup::updateOrCreate(
            ['invoice_number' => 'DEMO-INV-001'],
            [
                'payer_guest_id' => $guests['DEMO-GST-003']->id,
                'payment_status' => 'partial',
                'created_by' => $user->id,
            ]
        );
        $bookings['DEMO-BK-003']->update(['billing_group_id' => $billingGroup->id]);
        $bookings['DEMO-BK-004']->update(['billing_group_id' => $billingGroup->id]);

        $paymentDefinitions = [
            [
                'payment_number' => 'DEMO-PAY-001',
                'booking' => 'DEMO-BK-001',
                'amount' => (float) $bookings['DEMO-BK-001']->grand_total,
                'method' => 'cash',
                'notes' => 'Pembayaran lunas demo.',
            ],
            [
                'payment_number' => 'DEMO-PAY-002',
                'booking' => 'DEMO-BK-002',
                'amount' => 500000,
                'method' => 'transfer',
                'notes' => 'Pembayaran pertama demo.',
            ],
            [
                'payment_number' => 'DEMO-PAY-003',
                'booking' => 'DEMO-BK-002',
                'amount' => 300000,
                'method' => 'debit_card',
                'notes' => 'Pembayaran kedua demo.',
            ],
            [
                'payment_number' => 'DEMO-PAY-004',
                'billing_group_id' => $billingGroup->id,
                'amount' => 1000000,
                'method' => 'transfer',
                'notes' => 'Pembayaran awal tagihan gabungan demo.',
            ],
            [
                'payment_number' => 'DEMO-PAY-005',
                'billing_group_id' => $billingGroup->id,
                'amount' => 1000000,
                'method' => 'cash',
                'notes' => 'Pembayaran lanjutan tagihan gabungan demo.',
            ],
        ];

        foreach ($paymentDefinitions as $index => $attributes) {
            Payment::updateOrCreate(
                ['payment_number' => $attributes['payment_number']],
                [
                    'booking_id' => isset($attributes['booking']) ? $bookings[$attributes['booking']]->id : null,
                    'billing_group_id' => $attributes['billing_group_id'] ?? null,
                    'payment_date' => now()->subDays(4 - $index),
                    'amount' => $attributes['amount'],
                    'payment_method' => $attributes['method'],
                    'payment_status' => 'paid',
                    'notes' => $attributes['notes'],
                    'created_by' => $user->id,
                ]
            );
        }

        $billingPayments = [
            ['DEMO-PAY-004', 'DEMO-BK-003', 700000],
            ['DEMO-PAY-004', 'DEMO-BK-004', 300000],
            ['DEMO-PAY-005', 'DEMO-BK-004', 1000000],
        ];
        foreach ($billingPayments as [$paymentNumber, $bookingNumber, $amount]) {
            $payment = Payment::where('payment_number', $paymentNumber)->firstOrFail();
            DB::table('booking_payment_allocations')->updateOrInsert(
                ['payment_id' => $payment->id, 'booking_id' => $bookings[$bookingNumber]->id],
                ['amount' => $amount]
            );
        }

        $inventoryDefinitions = [
            ['category' => 'Demo Linen', 'name' => 'Demo Handuk', 'sku' => 'DEMO-INV-001', 'unit' => 'pcs', 'stock' => 40, 'minimum' => 10],
            ['category' => 'Demo Amenities', 'name' => 'Demo Sabun', 'sku' => 'DEMO-INV-002', 'unit' => 'pcs', 'stock' => 8, 'minimum' => 10],
            ['category' => 'Demo Housekeeping', 'name' => 'Demo Cairan Pembersih', 'sku' => 'DEMO-INV-003', 'unit' => 'liter', 'stock' => 25, 'minimum' => 5],
            ['category' => 'Demo F&B', 'name' => 'Demo Air Mineral', 'sku' => 'DEMO-INV-004', 'unit' => 'botol', 'stock' => 60, 'minimum' => 20],
            ['category' => 'Demo Maintenance', 'name' => 'Demo Lampu LED', 'sku' => 'DEMO-INV-005', 'unit' => 'pcs', 'stock' => 15, 'minimum' => 5],
        ];

        foreach ($inventoryDefinitions as $index => $attributes) {
            $category = InventoryCategory::updateOrCreate(
                ['name' => $attributes['category']],
                ['description' => 'Kategori inventaris data demo.']
            );
            $item = InventoryItem::updateOrCreate(
                ['sku' => $attributes['sku']],
                [
                    'category_id' => $category->id,
                    'name' => $attributes['name'],
                    'unit' => $attributes['unit'],
                    'current_stock' => $attributes['stock'],
                    'minimum_stock' => $attributes['minimum'],
                    'description' => 'Barang inventaris data demo.',
                ]
            );
            $before = $attributes['stock'] - 5;
            InventoryTransaction::updateOrCreate(
                ['notes' => 'DEMO-INV-TRX-00'.($index + 1)],
                [
                    'item_id' => $item->id,
                    'type' => 'stock_in',
                    'quantity' => 5,
                    'stock_before' => $before,
                    'stock_after' => $attributes['stock'],
                    'created_by' => $warehouseUser->id,
                ]
            );
        }
    }
}
