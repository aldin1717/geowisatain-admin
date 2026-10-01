<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\CheckInService;
use App\Services\CheckOutService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected CheckInService $checkInService,
        protected CheckOutService $checkOutService
    ) {}

    public function index(Request $request)
    {
        $query = Booking::with(['guest', 'room.roomType']);

        if ($search = $request->input('search')) {
            $query->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%");
                  });
        }

        if ($status = $request->input('status')) {
            $query->where('booking_status', $status);
        }

        $bookings = $query->latest()
            ->paginate(15)
            ->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $guests = Guest::orderBy('full_name')->get();
        // Only fetch active rooms
        $rooms = Room::with('roomType')->where('is_active', true)->orderBy('room_number')->get();
        
        return view('bookings.create', compact('guests', 'rooms'));
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking($request->validated());
            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Booking $booking)
    {
        $booking->load(['guest', 'room.roomType', 'creator', 'checkedInBy', 'checkedOutBy']);
        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        return view('bookings.edit', compact('booking'));
    }

    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        $booking->update($request->validated());

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking updated successfully.');
    }

    public function checkIn(Booking $booking)
    {
        try {
            $this->checkInService->process($booking);
            return back()->with('success', 'Check-in processed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function checkOut(Booking $booking)
    {
        try {
            $this->checkOutService->process($booking);
            return back()->with('success', 'Check-out processed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
