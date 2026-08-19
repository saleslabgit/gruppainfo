<?php

declare(strict_types=1);

namespace App\Domain\User;

enum UserStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Approved, self::Rejected], true),
            self::Rejected => $target === self::Pending,
            self::Approved => false,
        };
    }
}
