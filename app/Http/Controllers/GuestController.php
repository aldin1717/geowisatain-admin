<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\UpdateGuestRequest;
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
                  ->orWhere('identity_number', 'like', "%{$search}%")
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

    public function create()
    {
        return view('guests.create');
    }

    public function store(StoreGuestRequest $request)
    {
        $data = $request->validated();
        $data['guest_code'] = $this->generateGuestCode();

        Guest::create($data);

        return redirect()->route('guests.index')
            ->with('success', 'Guest created successfully.');
    }

    public function show(Guest $guest)
    {
        $guest->load(['bookings' => function ($query) {
            $query->with('room.roomType')->latest();
        }]);

        return view('guests.show', compact('guest'));
    }

    public function edit(Guest $guest)
    {
        return view('guests.edit', compact('guest'));
    }

    public function update(UpdateGuestRequest $request, Guest $guest)
    {
        $guest->update($request->validated());

        return redirect()->route('guests.show', $guest)
            ->with('success', 'Guest updated successfully.');
    }

    public function destroy(Guest $guest)
    {
        if ($guest->bookings()->whereIn('booking_status', ['confirmed', 'checked_in'])->exists()) {
            return back()->with('error', 'Cannot delete guest with active bookings.');
        }

        $guest->delete();

        return redirect()->route('guests.index')
            ->with('success', 'Guest deleted successfully.');
    }

    private function generateGuestCode(): string
    {
        $prefix = 'GST-' . date('Y') . '-';
        $lastGuest = Guest::where('guest_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastGuest) {
            return $prefix . '00001';
        }

        $lastNumber = (int) substr($lastGuest->guest_code, -5);
        return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}
