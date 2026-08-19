<?php

declare(strict_types=1);

namespace App\Domain\User;

enum UserAction: string
{
    case Created = 'created';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case TariffChanged = 'tariff_changed';
    case Disabled = 'disabled';
    case Enabled = 'enabled';
    case Deleted = 'deleted';
}
