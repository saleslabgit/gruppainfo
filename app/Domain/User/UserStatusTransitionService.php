<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Exceptions\InvalidStatusTransition;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UserStatusTransitionService
{
    public function transition(User $user, UserStatus $target): User
    {
        DB::transaction(function () use ($user, $target): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $current = $lockedUser->status;

            if (! $current->canTransitionTo($target)) {
                throw InvalidStatusTransition::from('user', $current->value, $target->value);
            }

            $lockedUser->status = $target;
            $lockedUser->save();
        });

        return $user->refresh();
    }
}
