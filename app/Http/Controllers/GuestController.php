<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('guest_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $guests = $query->withCount('bookings')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('guests.index', compact('guests'));
    }

    public function show(Guest $guest)
    {
        $guest->load(['bookings' => function ($query) {
            $query->with('room.roomType')->latest();
        }]);

        return view('guests.show', compact('guest'));
    }
}
