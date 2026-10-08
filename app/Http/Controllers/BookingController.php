<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\CheckInService;
use App\Services\CheckOutService;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected CheckInService $checkInService,
        protected CheckOutService $checkOutService,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $query = Booking::with(['guest', 'room.roomType', 'rooms.roomType']);

        if ($search = $request->input('search')) {
            $query->where(function ($bookings) use ($search) {
                $bookings->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($guests) use ($search) {
                      $guests->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('booking_status', $status);
        }

        if ($checkInDate = $request->input('check_in_date')) {
            $query->whereDate('check_in_date', $checkInDate);
        }

        if ($checkOutDate = $request->input('check_out_date')) {
            $query->whereDate('check_out_date', $checkOutDate);
        }

        $bookings = $query->latest()
            ->paginate(15)
            ->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $rooms = Room::with('roomType')->where('is_active', true)->orderBy('room_number')->get();
        $hotelRooms = $rooms->filter(fn (Room $room) => $room->roomType->category === 'room')->values();
        $ballrooms = $rooms->filter(fn (Room $room) => $room->roomType->category === 'ballroom')->values();

        return view('bookings.create', compact('hotelRooms', 'ballrooms'));
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
        $booking->load(['guest', 'room.roomType', 'creator', 'checkedInBy', 'checkedOutBy', 'billingGroup.payerGuest', 'payments' => fn ($query) => $query->latest()]);
        $selectedRoomIds = $booking->selectedRoomIds();
        $selectedRooms = Room::with('roomType')->whereIn('id', $selectedRoomIds)->get()
            ->sortBy(fn (Room $room) => array_search($room->id, $selectedRoomIds));

        return view('bookings.show', compact('booking', 'selectedRooms'));
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

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Check-in processed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function printCheckInReceipt(Booking $booking)
    {
        return view('bookings.receipt', $this->checkInReceiptData($booking));
    }

    public function downloadCheckInReceipt(Booking $booking)
    {
        return Pdf::loadView('bookings.receipt', $this->checkInReceiptData($booking))
            ->download('check-in-'.$booking->booking_number.'.pdf');
    }

    private function checkInReceiptData(Booking $booking): array
    {
        $booking->load(['guest', 'room.roomType']);
        $selectedRoomIds = $booking->selectedRoomIds();
        $selectedRooms = Room::with('roomType')->whereIn('id', $selectedRoomIds)->get()
            ->sortBy(fn (Room $room) => array_search($room->id, $selectedRoomIds));

        return compact('booking', 'selectedRooms');
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

    public function recordPayment(Request $request, Booking $booking)
    {
        if ($booking->billing_group_id) {
            return redirect()->route('billing-groups.show', $booking->billing_group_id)
                ->with('error', 'This booking belongs to a merged bill. Record payment from the merged bill page.');
        }

        $remaining = max((float) $booking->grand_total - $booking->totalPaid(), 0);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$remaining],
            'payment_method' => ['required', 'in:cash,transfer,debit_card,credit_card,other'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->paymentService->processPayment($booking, $validated);

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
