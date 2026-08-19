<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use App\Domain\Exceptions\InvalidStatusTransition;
use App\Models\Payment;

final class PaymentStatusTransitionService
{
    public function transition(Payment $payment, PaymentStatus $target): Payment
    {
        $current = $payment->status;

        if (! $current->canTransitionTo($target)) {
            throw InvalidStatusTransition::from('payment', $current->value, $target->value);
        }

        $payment->status = $target;
        $payment->save();

        return $payment;
    }
}
