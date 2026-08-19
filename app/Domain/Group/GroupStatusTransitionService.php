<?php

declare(strict_types=1);

namespace App\Domain\Group;

use App\Domain\Exceptions\InvalidStatusTransition;
use App\Models\Group;
use App\Models\GroupStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class GroupStatusTransitionService
{
    public function transition(
        Group $group,
        GroupStatus $target,
        ?User $actor = null,
        ?string $comment = null,
    ): Group {
        DB::transaction(function () use ($group, $target, $actor, $comment): void {
            $lockedGroup = Group::query()->lockForUpdate()->findOrFail($group->getKey());
            $current = $lockedGroup->status;

            if (! $current->canTransitionTo($target)) {
                throw InvalidStatusTransition::from('group', $current->value, $target->value);
            }

            $lockedGroup->status = $target;
            $lockedGroup->save();

            GroupStatusHistory::query()->create([
                'group_id' => $lockedGroup->getKey(),
                'from_status' => $current,
                'to_status' => $target,
                'actor_id' => $actor?->getKey(),
                'actor_type' => $actor === null ? 'system' : 'user',
                'comment' => $comment,
            ]);
        });

        return $group->refresh();
    }
}
