<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Models\User;
use App\Models\UserActionHistory;
use Illuminate\Support\Facades\DB;

final readonly class PsychologistAdminService
{
    public function __construct(
        private UserStatusTransitionService $statusTransitions,
        private UserSessionInvalidator $sessionInvalidator,
    ) {}

    /** @param array<string, mixed> $profile */
    public function create(array $profile, bool $free, User $actor): User
    {
        return DB::transaction(function () use ($profile, $free, $actor): User {
            $psychologist = User::query()->create($profile + [
                'status' => UserStatus::Pending,
                'disabled' => false,
                'free' => $free,
                'admin' => false,
                'password' => null,
            ]);

            $this->record($psychologist, $actor, UserAction::Created, metadata: ['free' => $free]);

            return $psychologist;
        });
    }

    public function transition(User $psychologist, UserStatus $target, User $actor): User
    {
        return DB::transaction(function () use ($psychologist, $target, $actor): User {
            $from = $psychologist->status;
            $updated = $this->statusTransitions->transition($psychologist, $target);

            $this->record(
                $updated,
                $actor,
                $target === UserStatus::Approved ? UserAction::Approved : UserAction::Rejected,
                $from,
                $target,
            );

            if ($target === UserStatus::Rejected) {
                $this->sessionInvalidator->invalidate($updated);
            }

            return $updated;
        });
    }

    public function changeTariff(User $psychologist, bool $free, User $actor): User
    {
        return DB::transaction(function () use ($psychologist, $free, $actor): User {
            $locked = User::query()->lockForUpdate()->findOrFail($psychologist->getKey());
            $before = $locked->free;
            $locked->free = $free;
            $locked->save();

            $this->record($locked, $actor, UserAction::TariffChanged, metadata: [
                'before' => $before,
                'after' => $free,
            ]);

            return $locked;
        });
    }

    public function setDisabled(User $psychologist, bool $disabled, User $actor): User
    {
        return DB::transaction(function () use ($psychologist, $disabled, $actor): User {
            $locked = User::query()->lockForUpdate()->findOrFail($psychologist->getKey());
            $before = $locked->disabled;
            $locked->disabled = $disabled;
            $locked->save();

            $this->record(
                $locked,
                $actor,
                $disabled ? UserAction::Disabled : UserAction::Enabled,
                metadata: ['before' => $before, 'after' => $disabled],
            );

            if ($disabled) {
                $this->sessionInvalidator->invalidate($locked);
            }

            return $locked;
        });
    }

    public function delete(User $psychologist, User $actor): void
    {
        DB::transaction(function () use ($psychologist, $actor): void {
            $locked = User::query()->lockForUpdate()->findOrFail($psychologist->getKey());
            $this->sessionInvalidator->invalidate($locked);
            $this->record($locked, $actor, UserAction::Deleted);
            $locked->delete();
        });
    }

    /** @param array<string, bool>|null $metadata */
    private function record(
        User $target,
        User $actor,
        UserAction $action,
        ?UserStatus $fromStatus = null,
        ?UserStatus $toStatus = null,
        ?array $metadata = null,
    ): void {
        UserActionHistory::query()->create([
            'target_user_id' => $target->getKey(),
            'actor_user_id' => $actor->getKey(),
            'actor_type' => 'user',
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'metadata' => $metadata,
        ]);
    }
}
