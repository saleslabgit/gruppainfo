<?php

declare(strict_types=1);

namespace App\Domain\User;

enum UserStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает решения',
            self::Approved => 'Принят',
            self::Rejected => 'Отклонён',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Approved, self::Rejected], true),
            self::Rejected => $target === self::Pending,
            self::Approved => false,
        };
    }
}
