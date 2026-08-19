<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Group\GroupStatus;
use App\Domain\Payment\PaymentStatus;
use App\Models\Group;
use App\Models\User;

final class GroupPolicy
{
    public function viewAny(User $actor): bool
    {
        return ! $actor->trashed();
    }

    public function create(User $actor): bool
    {
        return ! $actor->trashed();
    }

    public function view(User $actor, Group $group): bool
    {
        return ! $group->trashed() && ($actor->admin || $group->owner_id === $actor->getKey());
    }

    public function update(User $actor, Group $group): bool
    {
        if (! $this->view($actor, $group)) {
            return false;
        }

        return $actor->admin || in_array($group->status, [GroupStatus::Draft, GroupStatus::Revision], true);
    }

    public function submit(User $actor, Group $group): bool
    {
        return ! $actor->admin && $this->update($actor, $group);
    }

    public function delete(User $actor, Group $group): bool
    {
        if ($actor->admin || ! $this->view($actor, $group)
            || ! in_array($group->status, [GroupStatus::Draft, GroupStatus::Rejected], true)) {
            return false;
        }

        return ! $group->payments()->where('status', PaymentStatus::Succeeded->value)->exists();
    }

    public function moderate(User $actor, Group $group): bool
    {
        return $actor->admin && ! $group->trashed() && ! $group->owner->admin;
    }

    public function cleanup(User $actor, Group $group): bool
    {
        return $actor->admin && ! $group->trashed()
            && in_array($group->status, [GroupStatus::Draft, GroupStatus::AwaitingPayment], true);
    }
}
