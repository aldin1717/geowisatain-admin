<?php

namespace App\Http\Requests;

use App\Enums\RoomStatus;
use App\Services\RoomAvailabilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('room_ids') && $this->filled('room_id')) {
            $this->merge([
                'room_ids' => [$this->input('room_id')],
                'selection_type' => $this->input('selection_type', 'room'),
            ]);
        }

        if (is_array($this->input('additional_charge_breakdown'))) {
            $items = array_values(array_filter(
                $this->input('additional_charge_breakdown'),
                static fn ($item) => is_array($item)
                    && (trim((string) ($item['name'] ?? '')) !== '' || trim((string) ($item['amount'] ?? '')) !== '')
            ));

            $this->merge(['additional_charge_breakdown' => $items]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_full_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_address' => ['nullable', 'string', 'max:1000'],
            'selection_type' => ['required', 'in:room,ballroom,both'],
            'room_ids' => ['required', 'array', 'min:1'],
            'room_ids.*' => ['required', 'integer', 'distinct', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'num_guests' => ['required', 'integer', 'min:1'],
            'booking_type' => ['nullable', 'in:general,corporate,esdm,travel_agent,diklat'],
            'is_day_use' => ['nullable', 'boolean'],
            'is_bill_merged' => ['nullable', 'boolean'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'additional_charge' => ['nullable', 'numeric', 'min:0'],
            'additional_charge_breakdown' => ['nullable', 'array', 'max:30'],
            'additional_charge_breakdown.*.name' => ['required', 'string', 'max:100'],
            'additional_charge_breakdown.*.amount' => ['required', 'numeric', 'min:0.01'],
            'ballroom_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $selectionType = $this->input('selection_type');
            $roomIds = $this->input('room_ids', []);

            if (! is_array($roomIds) || empty($roomIds)) {
                return;
            }

            foreach ($roomIds as $roomId) {
                if (! is_int($roomId) && (! is_string($roomId) || ! ctype_digit($roomId))) {
                    return;
                }
            }

            $selectedRooms = \App\Models\Room::with('roomType')
                ->whereIn('id', $roomIds)
                ->where('is_active', true)
                ->get();

            if ($selectedRooms->count() !== count(array_unique($roomIds))) {
                $validator->errors()->add('room_ids', 'Select active rooms or ballrooms from the list.');

                return;
            }

            if ($selectedRooms->contains(fn ($room) => in_array($room->status, [
                RoomStatus::Cleaning,
                RoomStatus::Maintenance,
                RoomStatus::OutOfService,
            ], true))) {
                $validator->errors()->add('room_ids', 'Rooms that are being cleaned, under maintenance, or out of service cannot be booked.');

                return;
            }

            $hasRoom = $selectedRooms->contains(fn ($room) => $room->roomType->category === 'room');
            $hasBallroom = $selectedRooms->contains(fn ($room) => $room->roomType->category === 'ballroom');

            if (($selectionType === 'room' && ($hasBallroom || ! $hasRoom))
                || ($selectionType === 'ballroom' && ($hasRoom || ! $hasBallroom))
                || ($selectionType === 'both' && (! $hasRoom || ! $hasBallroom))) {
                $validator->errors()->add('room_ids', 'Selected entries must match the selected booking option.');

                return;
            }

            if ($validator->errors()->has('check_in_date') || $validator->errors()->has('check_out_date')) {
                return;
            }

            $availabilityService = app(RoomAvailabilityService::class);
            $unavailableRooms = $selectedRooms->filter(fn ($room) => ! $availabilityService->isAvailable(
                $room->id,
                $this->input('check_in_date'),
                $this->input('check_out_date')
            ));

            if ($unavailableRooms->isNotEmpty()) {
                $roomNumbers = $unavailableRooms->pluck('room_number')->join(', ');
                $validator->errors()->add('room_ids', "Room(s) {$roomNumbers} are not available for the selected dates because of an existing booking or room status.");
            }
        });
    }
}
