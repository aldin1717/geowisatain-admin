<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Shift;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    public function open(float $openingCash, string $attendantName): Shift
    {
        try {
            return DB::transaction(function () use ($openingCash, $attendantName): Shift {
                if (Shift::query()->whereNotNull('active_key')->lockForUpdate()->first()) {
                    throw ValidationException::withMessages([
                        'shift' => 'Masih ada shift yang aktif. Tutup shift tersebut sebelum membuka shift baru.',
                    ]);
                }

                return Shift::create([
                    'opened_by' => auth()->id(),
                    'attendant_name' => $attendantName,
                    'opened_at' => now(),
                    'opening_cash' => $openingCash,
                    'active_key' => 'active',
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'shift' => 'Shift lain baru saja dibuka. Tutup shift tersebut sebelum membuka shift baru.',
            ]);
        }
    }

    public function close(Shift $shift, float $closingCash, ?string $notes = null): Shift
    {
        return DB::transaction(function () use ($shift, $closingCash, $notes): Shift {
            $shift = Shift::query()->lockForUpdate()->findOrFail($shift->id);

            if ($shift->closed_at !== null || $shift->active_key === null) {
                throw ValidationException::withMessages([
                    'shift' => 'Shift ini sudah ditutup.',
                ]);
            }

            $cashPayments = (float) $shift->payments()
                ->where('payment_status', PaymentStatus::Paid->value)
                ->where('payment_method', PaymentMethod::Cash->value)
                ->sum('amount');
            $expectedCash = (float) $shift->opening_cash + $cashPayments;

            $shift->update([
                'closed_by' => auth()->id(),
                'closed_at' => now(),
                'closing_cash' => $closingCash,
                'expected_cash' => $expectedCash,
                'cash_variance' => $closingCash - $expectedCash,
                'closing_notes' => $notes,
                'active_key' => null,
            ]);

            return $shift;
        });
    }
}
