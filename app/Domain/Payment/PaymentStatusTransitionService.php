<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use App\Domain\Exceptions\InvalidStatusTransition;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

final class PaymentStatusTransitionService
{
    public function transition(Payment $payment, PaymentStatus $target): Payment
    {
        DB::transaction(function () use ($payment, $target): void {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());
            $current = $lockedPayment->status;

            if (! $current->canTransitionTo($target)) {
                throw InvalidStatusTransition::from('payment', $current->value, $target->value);
            }

            $lockedPayment->status = $target;
            $lockedPayment->save();
        });

        return $payment->refresh();
    }
}
