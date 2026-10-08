<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RoomInventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $bookingIds = DB::table('bookings')->pluck('id');
            $billingGroupIds = DB::table('bookings')
                ->whereNotNull('billing_group_id')
                ->distinct()
                ->pluck('billing_group_id');

            if ($bookingIds->isNotEmpty()) {
                DB::table('payments')->whereIn('booking_id', $bookingIds)->delete();
            }

            if ($billingGroupIds->isNotEmpty()) {
                DB::table('payments')->whereIn('billing_group_id', $billingGroupIds)->delete();
                DB::table('billing_groups')->whereIn('id', $billingGroupIds)->delete();
            }

            DB::table('bookings')->delete();
            DB::table('rooms')->delete();
            DB::table('room_types')->delete();

            $types = [];
            foreach ([
                'VIP',
                'Deluxe King',
                'Deluxe Twin',
                'Superior Twin',
                'Superior King',
                'Executive Twin',
                'Executive King',
                'Dalam Renovasi',
            ] as $name) {
                $slug = Str::slug($name);
                $types[$name] = RoomType::create([
                    'name' => $name,
                    'category' => 'room',
                    'slug' => $slug,
                    'description' => $name === 'Dalam Renovasi'
                        ? 'Tipe kamar belum tercantum pada denah; kamar sedang direnovasi.'
                        : null,
                ]);
            }

            $roomDefinitions = [];
            $addRooms = static function (
                array $numbers,
                string $type,
                int $floor,
                string $status = 'available'
            ) use (&$roomDefinitions): void {
                foreach ($numbers as $number) {
                    $roomDefinitions[] = [
                        'room_number' => (string) $number,
                        'type' => $type,
                        'floor' => (string) $floor,
                        'status' => $status,
                    ];
                }
            };
            $addRange = static function (
                int $start,
                int $end,
                string $type,
                int $floor,
                string $status = 'available'
            ) use ($addRooms): void {
                $addRooms(range($start, $end), $type, $floor, $status);
            };

            $addRooms([101, 114], 'VIP', 1);
            $addRooms([102, 115, 117, 120, 121, 125, 127], 'Deluxe Twin', 1);
            $addRange(103, 107, 'Deluxe King', 1);
            $addRange(128, 129, 'Deluxe King', 1);
            $addRange(108, 113, 'Superior Twin', 1);
            $addRooms([116, 118, 119, 122, 123, 124, 126], 'Superior Twin', 1);

            $addRooms([201, 216, 230], 'VIP', 2);
            $addRange(202, 207, 'Superior Twin', 2);
            $addRange(209, 215, 'Superior Twin', 2);
            $addRooms([231], 'Superior Twin', 2);
            $addRooms([217, 218, 219, 228, 229], 'Superior King', 2);
            $addRange(220, 227, 'Deluxe King', 2);

            $addRange(301, 308, 'Superior Twin', 3);
            $addRange(310, 317, 'Superior Twin', 3);
            $addRange(318, 319, 'Dalam Renovasi', 3, 'maintenance');
            $addRange(320, 322, 'Superior Twin', 3);
            $addRange(324, 335, 'Superior Twin', 3);

            $addRooms([401, 402, 415, 423, 425], 'Executive Twin', 4);
            $addRange(432, 435, 'Executive Twin', 4);
            $addRange(403, 414, 'Executive King', 4);
            $addRooms([416, 417, 418, 419, 420, 421, 422, 424, 426, 427, 428, 429, 430, 431], 'Dalam Renovasi', 4, 'maintenance');

            foreach ($roomDefinitions as $definition) {
                $isRenovating = $definition['status'] === 'maintenance';
                $price = match ($definition['type']) {
                    'Superior Twin', 'Superior King' => 300000,
                    'Dalam Renovasi' => 0,
                    default => 400000,
                };

                Room::create([
                    'room_number' => $definition['room_number'],
                    'room_type_id' => $types[$definition['type']]->id,
                    'floor' => $definition['floor'],
                    'capacity' => 2,
                    'price_per_night' => $price,
                    'status' => $definition['status'],
                    'description' => $isRenovating ? 'Sedang direnovasi.' : null,
                    'is_active' => true,
                ]);
            }
        });
    }
}
