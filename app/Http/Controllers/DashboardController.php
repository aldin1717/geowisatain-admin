<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Room;

class DashboardController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $canManageHotel = $user->isAdmin() || $user->isReceptionist();
        $canManageInventory = $user->isAdmin() || $user->isWarehouse();

        $todayArrivals = 0;
        $todayDepartures = 0;
        $availableRooms = 0;
        $activeRoomCount = 0;
        $recentBookings = collect();
        $lowStockCount = 0;
        $lowStockItems = collect();
        $recentTransactions = collect();

        if ($canManageHotel) {
            $todayArrivals = Booking::whereDate('check_in_date', today())
                ->where('booking_status', BookingStatus::Confirmed->value)
                ->count();
            $todayDepartures = Booking::whereDate('check_out_date', today())
                ->where('booking_status', BookingStatus::CheckedIn->value)
                ->count();
            $availableRooms = Room::where('is_active', true)
                ->where('status', RoomStatus::Available->value)
                ->whereHas('roomType', fn ($query) => $query->where('category', 'room'))
                ->count();
            $activeRoomCount = Room::where('is_active', true)
                ->whereHas('roomType', fn ($query) => $query->where('category', 'room'))
                ->count();
            $recentBookings = Booking::with(['guest', 'room'])
                ->latest()
                ->limit(5)
                ->get();
        }

        if ($canManageInventory) {
            $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')->count();
            $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')
                ->orderBy('current_stock')
                ->limit(5)
                ->get();
            $recentTransactions = InventoryTransaction::with(['item', 'creator'])
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('dashboard.index', compact(
            'user',
            'canManageHotel',
            'canManageInventory',
            'todayArrivals',
            'todayDepartures',
            'availableRooms',
            'activeRoomCount',
            'recentBookings',
            'lowStockCount',
            'lowStockItems',
            'recentTransactions',
        ));
    }
}
