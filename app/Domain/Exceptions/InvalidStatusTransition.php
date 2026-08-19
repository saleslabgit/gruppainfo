<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use DomainException;

final class InvalidStatusTransition extends DomainException
{
    public static function from(string $subject, string $from, string $to): self
    {
        return new self("Invalid {$subject} status transition from [{$from}] to [{$to}].");
    }
}
