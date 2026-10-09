<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShiftController extends Controller
{
    public function __construct(private ShiftService $shiftService) {}

    public function index()
    {
        $activeShift = Shift::query()
            ->whereNotNull('active_key')
            ->with('openedBy')
            ->first();
        $recentShifts = Shift::query()
            ->whereNotNull('closed_at')
            ->with(['openedBy', 'closedBy'])
            ->latest('closed_at')
            ->paginate(15);

        return view('shifts.index', compact('activeShift', 'recentShifts'));
    }

    public function show(Shift $shift)
    {
        $shift->load([
            'openedBy',
            'closedBy',
            'payments' => fn ($query) => $query->with(['creator', 'booking.guest', 'billingGroup.payerGuest'])
                ->orderBy('payment_date'),
        ]);

        $paidPayments = $shift->payments->where('payment_status', PaymentStatus::Paid);
        $methodTotals = collect(PaymentMethod::cases())
            ->mapWithKeys(fn (PaymentMethod $method) => [
                $method->value => (float) $paidPayments
                    ->filter(fn ($payment) => $payment->payment_method === $method)
                    ->sum('amount'),
            ]);
        $expectedCash = (float) $shift->opening_cash + $methodTotals[PaymentMethod::Cash->value];

        return view('shifts.show', compact('shift', 'methodTotals', 'expectedCash'));
    }

    public function open(Request $request)
    {
        $validated = $request->validate([
            'attendant_name' => ['required', 'string', 'max:255'],
            'opening_cash' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        try {
            $shift = $this->shiftService->open(
                (float) $validated['opening_cash'],
                $validated['attendant_name']
            );
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()->route('shifts.show', $shift)
            ->with('success', 'Shift berhasil dibuka.');
    }

    public function close(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'closing_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->shiftService->close(
                $shift,
                (float) $validated['closing_cash'],
                $validated['closing_notes'] ?? null
            );
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()->route('shifts.show', $shift)
            ->with('success', 'Shift berhasil ditutup dan rekap transaksi telah disimpan.');
    }
}
