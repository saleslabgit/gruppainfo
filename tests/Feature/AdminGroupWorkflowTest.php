<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Group\GroupStatus;
use App\Domain\Payment\PaymentStatus;
use App\Domain\Settings\SettingKey;
use App\Domain\Settings\SettingService;
use App\Domain\Settings\SettingType;
use App\Domain\User\UserStatus;
use App\Models\Dictionary;
use App\Models\DictionaryItem;
use App\Models\Group;
use App\Models\GroupStatusHistory;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AdminGroupWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_and_edits_draft_for_active_psychologist_without_payment_or_protected_writes(): void
    {
        [$format, $gender] = $this->dictionaryItems();
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('owner@example.test', ['free' => false]);

        $response = $this->actingAs($admin)->post(route('admin.groups.store'), ['owner_id' => $owner->getKey()] + $this->payload($format, $gender));
        $group = Group::query()->firstOrFail();
        $response->assertRedirect(route('admin.groups.show', $group));
        self::assertSame(GroupStatus::Draft, $group->status);
        self::assertFalse($group->free);
        self::assertSame(0, Payment::query()->count());

        $this->actingAs($admin)->put(route('admin.groups.update', $group), ['name' => 'Изменено', 'status' => 'active', 'owner_id' => $admin->getKey(), 'external_catalog_id' => 'bad'])
            ->assertRedirect(route('admin.groups.show', $group));
        $group->refresh();
        self::assertSame('Изменено', $group->name);
        self::assertSame(GroupStatus::Draft, $group->status);
        self::assertSame($owner->getKey(), $group->owner_id);
        self::assertNull($group->external_catalog_id);
    }

    public function test_revision_rejection_and_approval_require_valid_transition_and_preserve_history(): void
    {
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('owner@example.test');
        $revision = $this->group($owner, GroupStatus::Moderation);

        $this->actingAs($admin)->post(route('admin.groups.revision', $revision), ['comment' => '   '])->assertSessionHasErrors('comment');
        $this->actingAs($admin)->post(route('admin.groups.revision', $revision), ['comment' => 'Уточните расписание'])->assertRedirect();
        self::assertSame(GroupStatus::Revision, $revision->fresh()->status);
        self::assertSame('Уточните расписание', $revision->fresh()->moderator_comment);
        $this->assertDatabaseHas('gp_group_status_history', ['group_id' => $revision->getKey(), 'to_status' => 'revision', 'actor_id' => $admin->getKey(), 'comment' => 'Уточните расписание']);
        $this->actingAs($owner)->get(route('cabinet.groups.show', $revision))->assertOk()->assertSee('Уточните расписание')->assertSee('История статусов');

        $rejected = $this->group($owner, GroupStatus::Moderation);
        $this->actingAs($admin)->post(route('admin.groups.reject', $rejected), ['comment' => 'Причина отказа'])->assertRedirect();
        self::assertSame(GroupStatus::Rejected, $rejected->fresh()->status);
        self::assertSame('Причина отказа', $rejected->fresh()->rejection_reason);
        $this->actingAs($owner)->get(route('cabinet.groups.show', $rejected))->assertSee('Причина отказа');

        $approved = $this->group($owner, GroupStatus::Moderation);
        $this->actingAs($admin)->post(route('admin.groups.approve', $approved))->assertRedirect();
        self::assertSame(GroupStatus::Approved, $approved->fresh()->status);
        $this->assertDatabaseHas('gp_group_status_history', ['group_id' => $approved->getKey(), 'from_status' => 'moderation', 'to_status' => 'approved']);
    }

    public function test_psychologist_revises_and_resubmits_with_immutable_history(): void
    {
        [$format, $gender] = $this->dictionaryItems();
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('owner@example.test');
        $group = $this->group($owner, GroupStatus::Moderation, $this->storedPayload($format, $gender));
        $this->actingAs($admin)->post(route('admin.groups.revision', $group), ['comment' => 'Первый комментарий']);

        $this->actingAs($owner)->put(route('cabinet.groups.update', $group), ['schedule' => 'Новое расписание'])->assertRedirect();
        $this->actingAs($owner)->post(route('cabinet.groups.submit', $group))->assertSessionDoesntHaveErrors()->assertRedirect();

        self::assertSame(GroupStatus::Moderation, $group->fresh()->status);
        self::assertSame(2, $group->statusHistory()->count());
        $this->assertDatabaseHas('gp_group_status_history', ['group_id' => $group->getKey(), 'to_status' => 'revision', 'comment' => 'Первый комментарий']);
        $this->assertDatabaseHas('gp_group_status_history', ['group_id' => $group->getKey(), 'from_status' => 'revision', 'to_status' => 'moderation', 'actor_id' => $owner->getKey()]);
    }

    public function test_activation_uses_frozen_setting_and_utc_time_atomically(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-20 10:15:00', 'UTC'));
        Setting::query()->create(['key' => SettingKey::PlacementDurationDays, 'type' => SettingType::Integer, 'value' => '30']);
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('owner@example.test');
        $group = $this->group($owner, GroupStatus::Approved, ['expiry_warning_sent_at' => now('UTC')->subDay()]);

        $this->actingAs($admin)->post(route('admin.groups.activate', $group), ['external_catalog_id' => ' catalog-42 '])->assertRedirect();
        $group->refresh();
        self::assertSame(GroupStatus::Active, $group->status);
        self::assertSame('2026-08-20 10:15:00', $group->published_at?->setTimezone('UTC')->format('Y-m-d H:i:s'));
        self::assertSame(30, $group->placement_days);
        self::assertSame('2026-09-19 10:15:00', $group->expires_at?->setTimezone('UTC')->format('Y-m-d H:i:s'));
        self::assertNull($group->expiry_warning_sent_at);
        self::assertSame('catalog-42', $group->external_catalog_id);

        app(SettingService::class)->set(SettingKey::PlacementDurationDays, SettingType::Integer, 60);
        $group->refresh();
        self::assertSame(30, $group->placement_days);
        self::assertSame('2026-09-19 10:15:00', $group->expires_at?->setTimezone('UTC')->format('Y-m-d H:i:s'));
        CarbonImmutable::setTestNow();
    }

    public function test_invalid_transition_rolls_back_side_fields_and_history(): void
    {
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('owner@example.test');
        $group = $this->group($owner, GroupStatus::Active, ['moderator_comment' => 'Старый']);

        $this->actingAs($admin)->post(route('admin.groups.revision', $group), ['comment' => 'Подделка'])->assertRedirect()->assertSessionHas('error');
        $group->refresh();
        self::assertSame(GroupStatus::Active, $group->status);
        self::assertSame('Старый', $group->moderator_comment);
        self::assertSame(0, $group->statusHistory()->count());
    }

    public function test_admin_cleanup_is_limited_to_draft_states(): void
    {
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('owner@example.test');
        $draft = $this->group($owner, GroupStatus::Draft);
        $moderation = $this->group($owner, GroupStatus::Moderation);

        $this->actingAs($admin)->delete(route('admin.groups.destroy', $draft))->assertRedirect(route('admin.groups.index'));
        self::assertTrue($draft->fresh()->trashed());
        $this->actingAs($admin)->delete(route('admin.groups.destroy', $moderation))->assertForbidden();
        self::assertFalse($moderation->fresh()->trashed());
    }

    public function test_successful_payment_blocks_admin_cleanup_and_exposes_only_safe_payment_facts(): void
    {
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('owner@example.test');
        $group = $this->group($owner, GroupStatus::Draft);
        Payment::query()->create([
            'owner_id' => $owner->getKey(), 'group_id' => $group->getKey(), 'type' => 'placement',
            'amount' => 12345, 'currency' => 'BYN', 'transaction_id' => 'safe-transaction-42',
            'status' => PaymentStatus::Succeeded, 'paid_at' => '2026-08-20 08:00:00',
            'bank_response' => ['secret' => 'must-not-render'],
        ]);

        $this->actingAs($admin)->get(route('admin.groups.show', $group))
            ->assertOk()
            ->assertSee('Удаление группы заблокировано')
            ->assertSee('123,45 BYN')
            ->assertSee('safe-transaction-42')
            ->assertDontSee('must-not-render')
            ->assertDontSee('data-bs-target="#cleanup-group"', false);
        $this->actingAs($admin)->delete(route('admin.groups.destroy', $group))->assertForbidden();
        self::assertFalse($group->fresh()->trashed());

        $awaitingPayment = $this->group($owner, GroupStatus::AwaitingPayment);
        Payment::query()->create([
            'owner_id' => $owner->getKey(), 'group_id' => $awaitingPayment->getKey(), 'type' => 'placement',
            'amount' => 100, 'status' => PaymentStatus::Succeeded,
        ]);
        $this->actingAs($admin)->delete(route('admin.groups.destroy', $awaitingPayment))->assertForbidden();
        self::assertFalse($awaitingPayment->fresh()->trashed());

        $group->payments()->update(['status' => PaymentStatus::Refunded]);
        $this->actingAs($admin)->delete(route('admin.groups.destroy', $group))->assertRedirect(route('admin.groups.index'));
        self::assertTrue($group->fresh()->trashed());
    }

    public function test_paid_moderation_rejection_warns_about_manual_refund_but_remains_available(): void
    {
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('owner@example.test');
        $group = $this->group($owner, GroupStatus::Moderation, ['free' => false]);
        Payment::query()->create([
            'owner_id' => $owner->getKey(), 'group_id' => $group->getKey(), 'type' => 'placement',
            'amount' => 5000, 'currency' => 'BYN', 'transaction_id' => 'reject-payment-7',
            'status' => PaymentStatus::Succeeded, 'paid_at' => '2026-08-20 08:00:00',
        ]);

        $this->actingAs($admin)->get(route('admin.groups.show', $group))
            ->assertOk()->assertSee('Нужен ручной возврат')->assertSee('50,00 BYN')->assertSee('reject-payment-7')
            ->assertSee(route('admin.groups.reject', $group), false);
        $this->actingAs($admin)->post(route('admin.groups.reject', $group), ['comment' => 'Отказ после оплаты'])->assertRedirect();
        self::assertSame(GroupStatus::Rejected, $group->fresh()->status);
    }

    public function test_deleted_owner_and_history_actor_remain_truthful_without_n_plus_one(): void
    {
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('deleted-owner@example.test', ['first_name' => 'Анна', 'last_name' => 'Смирнова']);
        $group = $this->group($owner, GroupStatus::Moderation, ['name' => 'Группа удалённого владельца']);
        GroupStatusHistory::query()->create([
            'group_id' => $group->getKey(), 'from_status' => GroupStatus::Draft,
            'to_status' => GroupStatus::Moderation, 'actor_type' => 'user', 'actor_id' => $owner->getKey(),
            'created_at' => now('UTC')->subMinute(),
        ]);
        GroupStatusHistory::query()->create([
            'group_id' => $group->getKey(), 'from_status' => GroupStatus::Moderation,
            'to_status' => GroupStatus::Approved, 'actor_type' => 'system', 'actor_id' => null,
            'created_at' => now('UTC'),
        ]);
        $owner->delete();

        $this->actingAs($admin)->get(route('admin.groups.index'))->assertOk()
            ->assertSee('Группа удалённого владельца')->assertSee('deleted-owner@example.test');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get(route('admin.groups.show', $group))->assertOk()
            ->assertSee('Смирнова Анна')->assertSee('deleted-owner@example.test')->assertSee('Система');
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();
        self::assertLessThanOrEqual(10, $count);
    }

    public function test_admin_list_search_filters_sort_pagination_and_query_count_are_bounded(): void
    {
        $admin = $this->user('admin@example.test', ['admin' => true]);
        $owner = $this->user('owner@example.test', ['first_name' => 'Марина']);
        $target = $this->group($owner, GroupStatus::Approved, ['name' => 'Целевая', 'free' => true]);
        $this->group($owner, GroupStatus::Expired, ['name' => 'Другая', 'free' => false]);

        $this->actingAs($admin)->get(route('admin.groups.index', ['search' => (string) $target->getKey(), 'status' => 'approved', 'tariff' => 'free', 'quick' => 'awaiting_publication', 'sort' => 'expires_at', 'direction' => 'asc']))
            ->assertOk()->assertSee('Целевая')->assertDontSee('Другая');
        for ($index = 0; $index < 24; $index++) {
            $this->group($owner, GroupStatus::Draft, ['name' => 'Страница '.$index]);
        }
        $this->actingAs($admin)->get(route('admin.groups.index', ['status' => 'draft', 'sort' => 'created_at', 'direction' => 'desc']))
            ->assertOk()->assertSee('status=draft&amp;sort=created_at&amp;direction=desc&amp;page=2', false);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get(route('admin.groups.index'))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();
        self::assertLessThanOrEqual(9, $count);
    }

    /** @return array{DictionaryItem, DictionaryItem} */
    private function dictionaryItems(): array
    {
        $format = Dictionary::query()->create(['code' => 'group_format', 'name' => 'Формат']);
        $gender = Dictionary::query()->create(['code' => 'gender', 'name' => 'Пол']);

        return [DictionaryItem::query()->create(['dictionary_id' => $format->getKey(), 'code' => 'test-online', 'name' => 'Онлайн']), DictionaryItem::query()->create(['dictionary_id' => $gender->getKey(), 'code' => 'test-any', 'name' => 'Любой'])];
    }

    /** @param array<string, mixed> $attributes */
    private function user(string $email, array $attributes = []): User
    {
        return User::query()->create(array_merge(['email' => $email, 'password' => 'secret-password', 'status' => UserStatus::Approved, 'disabled' => false, 'admin' => false, 'free' => false], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    private function group(User $owner, GroupStatus $status, array $attributes = []): Group
    {
        return Group::query()->forceCreate(array_merge(['owner_id' => $owner->getKey(), 'status' => $status, 'disabled' => false, 'free' => $owner->free], $attributes));
    }

    /** @return array<string, mixed> */
    private function payload(DictionaryItem $format, DictionaryItem $gender): array
    {
        return ['name' => 'Группа', 'description' => 'Описание', 'schedule' => 'По пятницам', 'format_id' => $format->getKey(), 'meeting_duration_minutes' => 90, 'participant_count' => 8, 'gender_id' => $gender->getKey(), 'price_per_meeting' => '25,00'];
    }

    /** @return array<string, mixed> */
    private function storedPayload(DictionaryItem $format, DictionaryItem $gender): array
    {
        return array_merge($this->payload($format, $gender), ['price_per_meeting' => 2500]);
    }
}
