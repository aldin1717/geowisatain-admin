<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('room_types', 'name')->ignore($this->room_type)],
            'category' => ['required', 'in:room,ballroom'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
