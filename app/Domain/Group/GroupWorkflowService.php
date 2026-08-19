<?php

declare(strict_types=1);

namespace App\Domain\Group;

use App\Domain\Settings\SettingKey;
use App\Domain\Settings\SettingService;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use UnexpectedValueException;

final class GroupWorkflowService
{
    public function __construct(
        private readonly GroupStatusTransitionService $transitions,
        private readonly SettingService $settings,
    ) {}

    public function createDraft(User $owner): Group
    {
        return Group::query()->forceCreate([
            'owner_id' => $owner->getKey(),
            'status' => GroupStatus::Draft,
            'disabled' => false,
            'free' => $owner->free,
        ]);
    }

    /** @param array<string, mixed> $data */
    public function updateContent(Group $group, User $actor, array $data): Group
    {
        return DB::transaction(function () use ($group, $actor, $data): Group {
            $locked = Group::query()->lockForUpdate()->findOrFail($group->getKey());
            Gate::forUser($actor)->authorize('update', $locked);
            $locked->update($data);

            return $locked->refresh();
        });
    }

    public function delete(Group $group, User $actor, string $ability): void
    {
        DB::transaction(function () use ($group, $actor, $ability): void {
            $locked = Group::query()->lockForUpdate()->findOrFail($group->getKey());
            Gate::forUser($actor)->authorize($ability, $locked);
            $locked->delete();
        });
    }

    /** @param array<string, mixed> $data */
    public function submit(Group $group, User $actor, array $data): Group
    {
        return DB::transaction(function () use ($group, $actor, $data): Group {
            $locked = Group::query()->lockForUpdate()->findOrFail($group->getKey());
            Gate::forUser($actor)->authorize('submit', $locked);
            $locked->update($data);

            return $this->transitions->transition($locked, GroupStatus::Moderation, $actor);
        });
    }

    public function requestRevision(Group $group, User $actor, string $comment): Group
    {
        return DB::transaction(function () use ($group, $actor, $comment): Group {
            $locked = Group::query()->lockForUpdate()->findOrFail($group->getKey());
            $locked->forceFill(['moderator_comment' => $comment, 'rejection_reason' => null])->save();

            return $this->transitions->transition($locked, GroupStatus::Revision, $actor, $comment);
        });
    }

    public function reject(Group $group, User $actor, string $reason): Group
    {
        return DB::transaction(function () use ($group, $actor, $reason): Group {
            $locked = Group::query()->lockForUpdate()->findOrFail($group->getKey());
            $locked->forceFill(['rejection_reason' => $reason])->save();

            return $this->transitions->transition($locked, GroupStatus::Rejected, $actor, $reason);
        });
    }

    public function approve(Group $group, User $actor): Group
    {
        return $this->transitions->transition($group, GroupStatus::Approved, $actor);
    }

    public function activate(Group $group, User $actor, ?string $externalCatalogId): Group
    {
        return DB::transaction(function () use ($group, $actor, $externalCatalogId): Group {
            $locked = Group::query()->lockForUpdate()->findOrFail($group->getKey());
            $days = $this->settings->integer(SettingKey::PlacementDurationDays);

            if ($days === null || $days <= 0) {
                throw new UnexpectedValueException('Срок размещения должен быть положительным целым числом.');
            }

            $publishedAt = now('UTC')->toImmutable();
            $locked->forceFill([
                'published_at' => $publishedAt,
                'placement_days' => $days,
                'expires_at' => $publishedAt->addDays($days),
                'expiry_warning_sent_at' => null,
                'external_catalog_id' => $externalCatalogId,
            ])->save();

            return $this->transitions->transition($locked, GroupStatus::Active, $actor);
        });
    }
}
