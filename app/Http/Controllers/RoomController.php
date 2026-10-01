<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('roomType');

        if ($search = $request->input('search')) {
            $query->where('room_number', 'like', "%{$search}%");
        }

        if ($type = $request->input('room_type_id')) {
            $query->where('room_type_id', $type);
        }

        $rooms = $query->latest()
            ->paginate(15)
            ->withQueryString();

        $roomTypes = RoomType::where('is_active', true)->get();

        return view('rooms.index', compact('rooms', 'roomTypes'));
    }

    public function create()
    {
        $roomTypes = RoomType::where('is_active', true)->get();
        return view('rooms.create', compact('roomTypes'));
    }

    public function store(StoreRoomRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('rooms', 'public');
        }

        Room::create($data);

        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        $room->load('roomType', 'bookings');
        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $roomTypes = RoomType::where('is_active', true)->get();
        return view('rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $data['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($data);

        return redirect()->route('rooms.show', $room)
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        if ($room->bookings()->whereIn('booking_status', ['confirmed', 'checked_in'])->exists()) {
            return back()->with('error', 'Cannot delete room with active bookings.');
        }

        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }

        $room->delete();

        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
