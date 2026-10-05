<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:room_types,name'],
            'category' => ['required', 'in:room,ballroom'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
