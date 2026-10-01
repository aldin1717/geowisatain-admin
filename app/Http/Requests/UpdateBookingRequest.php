<?php

namespace App\Http\Requests;

use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_status' => ['required', Rule::enum(BookingStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            // In a real system, you might allow modifying dates if it's still pending/confirmed,
            // but for simplicity we'll just allow status and notes updates after creation.
        ];
    }
}
