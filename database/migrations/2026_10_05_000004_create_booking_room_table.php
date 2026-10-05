<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_room', function (Blueprint $table) {
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->primary(['booking_id', 'room_id']);
        });

        DB::table('bookings')->select(['id', 'room_id', 'additional_room_ids'])->orderBy('id')->chunk(500, function ($bookings) {
            $rows = [];

            foreach ($bookings as $booking) {
                $additionalRoomIds = json_decode($booking->additional_room_ids ?? '[]', true);
                $additionalRoomIds = is_array($additionalRoomIds) ? $additionalRoomIds : [];
                $roomIds = array_unique(array_merge([$booking->room_id], $additionalRoomIds));

                foreach ($roomIds as $roomId) {
                    $rows[] = ['booking_id' => $booking->id, 'room_id' => $roomId];
                }
            }

            if ($rows !== []) {
                DB::table('booking_room')->insertOrIgnore($rows);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_room');
    }
};
