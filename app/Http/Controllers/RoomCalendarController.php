<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class RoomCalendarController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'floor' => ['nullable', 'string', 'max:50'],
        ]);
        $month = CarbonImmutable::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $monthEnd = $month->addMonth();
        $days = [];

        for ($day = $month; $day->lt($monthEnd); $day = $day->addDay()) {
            $days[] = $day;
        }

        $roomsQuery = Room::with('roomType')
            ->where('is_active', true)
            ->orderBy('floor')
            ->orderBy('room_number');
        if (! empty($validated['floor'])) {
            $roomsQuery->where('floor', $validated['floor']);
        }
        $rooms = $roomsQuery->get();
        $floorOptions = Room::query()
            ->where('is_active', true)
            ->whereNotNull('floor')
            ->distinct()
            ->pluck('floor')
            ->sortBy(fn ($floor) => (int) $floor)
            ->values();
        $bookings = Booking::with(['guest', 'rooms:id'])
            ->whereIn('booking_status', [
                BookingStatus::Confirmed->value,
                BookingStatus::CheckedIn->value,
            ])
            ->where('check_in_date', '<', $monthEnd->toDateString())
            ->where('check_out_date', '>', $month->toDateString())
            ->get();

        $bookingsByRoom = [];
        foreach ($bookings as $booking) {
            foreach ($booking->selectedRoomIds() as $roomId) {
                $bookingsByRoom[$roomId][] = $booking;
            }
        }

        $blockedRoomStatuses = [
            RoomStatus::Cleaning,
            RoomStatus::Maintenance,
            RoomStatus::OutOfService,
        ];
        $calendar = $rooms->map(function (Room $room) use ($days, $bookingsByRoom, $blockedRoomStatuses) {
            $cells = [];

            foreach ($days as $day) {
                $booking = collect($bookingsByRoom[$room->id] ?? [])->first(
                    fn (Booking $candidate) => $candidate->check_in_date->lte($day)
                        && $candidate->check_out_date->gt($day)
                );

                $cells[] = [
                    'date' => $day,
                    'booking' => $booking,
                    'status' => $booking
                        ? ($booking->booking_status === BookingStatus::CheckedIn ? 'occupied' : 'reserved')
                        : (in_array($room->status, $blockedRoomStatuses, true) ? $room->status->value : 'available'),
                ];
            }

            return [
                'room' => $room,
                'cells' => $cells,
            ];
        });

        return view('room-calendar.index', [
            'month' => $month,
            'days' => $days,
            'calendar' => $calendar,
            'floorOptions' => $floorOptions,
            'selectedFloor' => $validated['floor'] ?? '',
        ]);
    }
}
