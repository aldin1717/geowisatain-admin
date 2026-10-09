<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\BillingGroup;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingGroupController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index()
    {
        $billingGroups = BillingGroup::with('payerGuest')
            ->withCount('bookings')
            ->latest()
            ->paginate(15);

        return view('billing-groups.index', compact('billingGroups'));
    }

    public function create()
    {
        $bookings = Booking::with(['guest', 'room.roomType'])
            ->whereNull('billing_group_id')
            ->whereIn('payment_status', [PaymentStatus::Unpaid->value, PaymentStatus::Partial->value])
            ->whereIn('booking_status', ['confirmed', 'checked_in'])
            ->latest()
            ->get();

        $guests = $bookings->pluck('guest')->unique('id')->sortBy('full_name')->values();

        return view('billing-groups.create', compact('bookings', 'guests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_ids' => ['required', 'array', 'min:2'],
            'booking_ids.*' => ['required', 'integer', 'distinct', 'exists:bookings,id'],
            'payer_guest_id' => ['required', 'integer', 'exists:guests,id'],
        ]);

        $billingGroup = DB::transaction(function () use ($validated) {
            $bookingIds = array_map('intval', $validated['booking_ids']);
            $bookings = Booking::with('guest')
                ->whereIn('id', $bookingIds)
                ->whereNull('billing_group_id')
                ->whereIn('payment_status', [PaymentStatus::Unpaid->value, PaymentStatus::Partial->value])
                ->whereIn('booking_status', ['confirmed', 'checked_in'])
                ->lockForUpdate()
                ->get();

            if ($bookings->count() !== count($bookingIds)) {
                throw ValidationException::withMessages([
                    'booking_ids' => 'One or more bookings have already been paid or assigned to another bill.',
                ]);
            }

            if (! $bookings->contains('guest_id', (int) $validated['payer_guest_id'])) {
                throw ValidationException::withMessages([
                    'payer_guest_id' => 'The payer must be one of the guests on the selected bookings.',
                ]);
            }

            $billingGroup = BillingGroup::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'payer_guest_id' => $validated['payer_guest_id'],
                'payment_status' => PaymentStatus::Unpaid->value,
                'created_by' => auth()->id(),
            ]);

            Booking::whereIn('id', $bookingIds)->update([
                'billing_group_id' => $billingGroup->id,
                'is_bill_merged' => true,
            ]);

            $billingGroup->load('bookings');
            if ($billingGroup->paidAmount() > 0) {
                $billingGroup->update(['payment_status' => PaymentStatus::Partial->value]);
            }

            return $billingGroup;
        });

        return redirect()->route('billing-groups.show', $billingGroup)
            ->with('success', 'The selected bookings are now combined into one bill.');
    }

    public function show(BillingGroup $billingGroup)
    {
        $billingGroup->load([
            'payerGuest',
            'bookings.guest',
            'bookings.rooms.roomType',
            'payments' => fn ($query) => $query->with('creator')->latest(),
        ]);

        return view('billing-groups.show', compact('billingGroup'));
    }

    public function recordPayment(Request $request, BillingGroup $billingGroup)
    {
        $remaining = $billingGroup->outstandingAmount();
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$remaining],
            'payment_method' => ['required', 'in:cash,transfer,debit_card,credit_card,other'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->paymentService->processBillingGroupPayment($billingGroup, $validated);
        } catch (\InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['amount' => $exception->getMessage()]);
        } catch (\DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('billing-groups.show', $billingGroup)
            ->with('success', 'Merged bill payment recorded successfully.');
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.date('Y').'-';
        $lastGroup = BillingGroup::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();
        $lastNumber = $lastGroup ? (int) substr($lastGroup->invoice_number, -5) : 0;

        return $prefix.str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}
