<?php

declare(strict_types=1);

namespace App\Domain\Group;

enum GroupStatus: string
{
    case AwaitingPayment = 'awaiting_payment';
    case Draft = 'draft';
    case Moderation = 'moderation';
    case Revision = 'revision';
    case Rejected = 'rejected';
    case Approved = 'approved';
    case Active = 'active';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingPayment => 'Ожидает оплаты',
            self::Draft => 'Черновик',
            self::Moderation => 'На модерации',
            self::Revision => 'На доработке',
            self::Rejected => 'Отклонена',
            self::Approved => 'Одобрена, ожидает публикации',
            self::Active => 'Активная',
            self::Expired => 'Закончена',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft, self::AwaitingPayment => 'neutral',
            self::Moderation => 'info',
            self::Revision, self::Approved => 'warning',
            self::Rejected => 'danger',
            self::Active => 'success',
            self::Expired => 'neutral',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::AwaitingPayment => $target === self::Draft,
            self::Draft => $target === self::Moderation,
            self::Moderation => in_array($target, [self::Revision, self::Rejected, self::Approved], true),
            self::Revision => $target === self::Moderation,
            self::Approved => $target === self::Active,
            self::Active => $target === self::Expired,
            self::Expired => $target === self::Approved,
            self::Rejected => false,
        };
    }
}
