<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Exceptions\InvalidStatusTransition;
use App\Models\User;

final class UserStatusTransitionService
{
    public function transition(User $user, UserStatus $target): User
    {
        $current = $user->status;

        if (! $current->canTransitionTo($target)) {
            throw InvalidStatusTransition::from('user', $current->value, $target->value);
        }

        $user->status = $target;
        $user->save();

        return $user;
    }
}
