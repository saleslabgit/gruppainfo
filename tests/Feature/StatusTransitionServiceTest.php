<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Exceptions\InvalidStatusTransition;
use App\Domain\Group\GroupStatus;
use App\Domain\Group\GroupStatusTransitionService;
use App\Domain\Payment\PaymentStatus;
use App\Domain\Payment\PaymentStatusTransitionService;
use App\Domain\User\UserStatus;
use App\Domain\User\UserStatusTransitionService;
use App\Models\Group;
use App\Models\GroupStatusHistory;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StatusTransitionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_service_enforces_every_transition(): void
    {
        $service = new UserStatusTransitionService;
        $sequence = 0;

        foreach (UserStatus::cases() as $from) {
            foreach (UserStatus::cases() as $to) {
                $user = $this->createUser("user-{$sequence}@example.test", $from);
                $sequence++;

                if ($from->canTransitionTo($to)) {
                    $service->transition($user, $to);
                    self::assertSame($to, $user->fresh()->status);
                } else {
                    try {
                        $service->transition($user, $to);
                        self::fail("Forbidden user transition {$from->value} -> {$to->value} succeeded.");
                    } catch (InvalidStatusTransition) {
                        self::assertSame($from, $user->fresh()->status);
                    }
                }
            }
        }
    }

    public function test_stale_user_cannot_overwrite_current_status(): void
    {
        $user = $this->createUser('stale-user@example.test', UserStatus::Pending);
        $current = User::query()->findOrFail($user->getKey());
        $stale = User::query()->findOrFail($user->getKey());
        $service = new UserStatusTransitionService;

        $transitioned = $service->transition($current, UserStatus::Approved);
        self::assertSame(UserStatus::Approved, $transitioned->status);

        try {
            $service->transition($stale, UserStatus::Rejected);
            self::fail('A stale user overwrote the current approved status.');
        } catch (InvalidStatusTransition) {
            self::assertSame(UserStatus::Approved, User::query()->findOrFail($user->getKey())->status);
        }
    }

    public function test_payment_service_enforces_every_transition(): void
    {
        $service = new PaymentStatusTransitionService;
        $owner = $this->createUser('payment-owner@example.test', UserStatus::Approved);
        $group = $this->createGroup($owner, GroupStatus::Draft);

        foreach (PaymentStatus::cases() as $from) {
            foreach (PaymentStatus::cases() as $to) {
                $payment = Payment::query()->create([
                    'owner_id' => $owner->getKey(),
                    'group_id' => $group->getKey(),
                    'type' => 'placement',
                    'amount' => 100,
                    'status' => $from,
                ]);

                if ($from->canTransitionTo($to)) {
                    $service->transition($payment, $to);
                    self::assertSame($to, $payment->fresh()->status);
                } else {
                    try {
                        $service->transition($payment, $to);
                        self::fail("Forbidden payment transition {$from->value} -> {$to->value} succeeded.");
                    } catch (InvalidStatusTransition) {
                        self::assertSame($from, $payment->fresh()->status);
                    }
                }
            }
        }
    }

    public function test_stale_payment_cannot_overwrite_current_status(): void
    {
        $owner = $this->createUser('stale-payment-owner@example.test', UserStatus::Approved);
        $group = $this->createGroup($owner, GroupStatus::Draft);
        $payment = Payment::query()->create([
            'owner_id' => $owner->getKey(),
            'group_id' => $group->getKey(),
            'type' => 'placement',
            'amount' => 100,
            'status' => PaymentStatus::Pending,
        ]);
        $current = Payment::query()->findOrFail($payment->getKey());
        $stale = Payment::query()->findOrFail($payment->getKey());
        $service = new PaymentStatusTransitionService;

        $transitioned = $service->transition($current, PaymentStatus::Succeeded);
        self::assertSame(PaymentStatus::Succeeded, $transitioned->status);

        try {
            $service->transition($stale, PaymentStatus::Failed);
            self::fail('A stale payment overwrote the current succeeded status.');
        } catch (InvalidStatusTransition) {
            self::assertSame(PaymentStatus::Succeeded, Payment::query()->findOrFail($payment->getKey())->status);
        }
    }

    public function test_group_service_enforces_every_transition_and_records_only_successes(): void
    {
        $service = new GroupStatusTransitionService;
        $owner = $this->createUser('group-owner@example.test', UserStatus::Approved);

        foreach (GroupStatus::cases() as $from) {
            foreach (GroupStatus::cases() as $to) {
                $group = $this->createGroup($owner, $from);

                if ($from->canTransitionTo($to)) {
                    $service->transition($group, $to);
                    self::assertSame($to, $group->fresh()->status);
                    self::assertSame(1, $group->statusHistory()->count());
                    $history = $group->statusHistory()->firstOrFail();
                    self::assertInstanceOf(GroupStatusHistory::class, $history);
                    self::assertSame('system', $history->actor_type);
                } else {
                    try {
                        $service->transition($group, $to);
                        self::fail("Forbidden group transition {$from->value} -> {$to->value} succeeded.");
                    } catch (InvalidStatusTransition) {
                        self::assertSame($from, $group->fresh()->status);
                        self::assertSame(0, $group->statusHistory()->count());
                    }
                }
            }
        }
    }

    public function test_group_history_records_user_actor_and_comment(): void
    {
        $owner = $this->createUser('history-owner@example.test', UserStatus::Approved);
        $actor = $this->createUser('history-actor@example.test', UserStatus::Approved);
        $group = $this->createGroup($owner, GroupStatus::Moderation);

        (new GroupStatusTransitionService)->transition(
            $group,
            GroupStatus::Revision,
            $actor,
            'Уточните расписание.',
        );

        $history = $group->statusHistory()->firstOrFail();
        self::assertInstanceOf(GroupStatusHistory::class, $history);
        self::assertSame(GroupStatus::Moderation, $history->from_status);
        self::assertSame(GroupStatus::Revision, $history->to_status);
        self::assertTrue($history->actor->is($actor));
        self::assertSame('user', $history->actor_type);
        self::assertSame('Уточните расписание.', $history->comment);
    }

    public function test_group_status_change_rolls_back_when_history_write_fails(): void
    {
        $owner = $this->createUser('rollback-owner@example.test', UserStatus::Approved);
        $group = $this->createGroup($owner, GroupStatus::Draft);

        try {
            (new GroupStatusTransitionService)->transition(
                $group,
                GroupStatus::Moderation,
                null,
                str_repeat('x', 70000),
            );
            self::fail('Oversized history comment was accepted.');
        } catch (QueryException) {
            self::assertSame(GroupStatus::Draft, $group->fresh()->status);
            self::assertSame(0, $group->statusHistory()->count());
        }
    }

    private function createUser(string $email, UserStatus $status): User
    {
        return User::query()->create([
            'email' => $email,
            'status' => $status,
            'password' => 'test-password',
        ]);
    }

    private function createGroup(User $owner, GroupStatus $status): Group
    {
        return Group::query()->create([
            'owner_id' => $owner->getKey(),
            'status' => $status,
        ]);
    }
}
