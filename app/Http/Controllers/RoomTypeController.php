<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomTypeRequest;
use App\Http\Requests\UpdateRoomTypeRequest;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RoomTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RoomType::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $roomTypes = $query->withCount('rooms')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('room_types.index', compact('roomTypes'));
    }

    public function create()
    {
        return view('room_types.create');
    }

    public function store(StoreRoomTypeRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('room_types', 'public');
        }

        RoomType::create($data);

        return redirect()->route('room-types.index')
            ->with('success', 'Room type created successfully.');
    }

    public function show(RoomType $roomType)
    {
        $roomType->load('rooms');
        return view('room_types.show', compact('roomType'));
    }

    public function edit(RoomType $roomType)
    {
        return view('room_types.edit', compact('roomType'));
    }

    public function update(UpdateRoomTypeRequest $request, RoomType $roomType)
    {
        $data = $request->validated();
        
        if ($data['name'] !== $roomType->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            if ($roomType->image) {
                Storage::disk('public')->delete($roomType->image);
            }
            $data['image'] = $request->file('image')->store('room_types', 'public');
        }

        $roomType->update($data);

        return redirect()->route('room-types.show', $roomType)
            ->with('success', 'Room type updated successfully.');
    }

    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->exists()) {
            return back()->with('error', 'Cannot delete room type because it has associated rooms.');
        }

        if ($roomType->image) {
            Storage::disk('public')->delete($roomType->image);
        }

        $roomType->delete();

        return redirect()->route('room-types.index')
            ->with('success', 'Room type deleted successfully.');
    }
}
