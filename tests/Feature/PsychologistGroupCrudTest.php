<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Group\GroupStatus;
use App\Domain\Payment\PaymentStatus;
use App\Domain\User\UserStatus;
use App\Models\Dictionary;
use App\Models\DictionaryItem;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PsychologistGroupCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_and_paid_psychologists_create_direct_drafts_without_payments(): void
    {
        foreach ([true, false] as $free) {
            $owner = $this->user(($free ? 'free' : 'paid').'@example.test', ['free' => $free]);
            $response = $this->actingAs($owner)->post(route('cabinet.groups.store'));
            $group = Group::query()->whereBelongsTo($owner, 'owner')->firstOrFail();

            $response->assertRedirect(route('cabinet.groups.edit', $group));
            self::assertSame(GroupStatus::Draft, $group->status);
            self::assertSame($free, $group->free);
            self::assertNotEmpty($group->public_uuid);
            self::assertFalse($group->disabled);
            self::assertSame(0, $group->payments()->count());
        }

        self::assertSame(0, Payment::query()->count());
    }

    public function test_draft_save_uses_exact_money_and_ignores_protected_fields(): void
    {
        [$format, $gender] = $this->dictionaryItems();
        $owner = $this->user('owner@example.test', ['free' => true]);
        $other = $this->user('other@example.test');
        $group = $this->group($owner);

        $this->actingAs($owner)->put(route('cabinet.groups.update', $group), array_merge($this->groupPayload($format, $gender), [
            'price_per_meeting' => '123,45',
            'owner_id' => $other->getKey(), 'status' => 'active', 'free' => '0', 'disabled' => '1',
            'public_uuid' => 'injected', 'external_catalog_id' => 'injected', 'placement_days' => 999,
        ]))->assertRedirect(route('cabinet.groups.show', $group));

        $group->refresh();
        self::assertSame(12345, $group->price_per_meeting);
        self::assertSame($owner->getKey(), $group->owner_id);
        self::assertSame(GroupStatus::Draft, $group->status);
        self::assertTrue($group->free);
        self::assertFalse($group->disabled);
        self::assertNotSame('injected', $group->public_uuid);
        self::assertNull($group->external_catalog_id);
        self::assertNull($group->placement_days);

        $this->actingAs($owner)->put(route('cabinet.groups.update', $group), ['price_per_meeting' => '12.345'])
            ->assertSessionHasErrors('price_per_meeting');
        self::assertSame(12345, $group->fresh()->price_per_meeting);
    }

    public function test_submission_requires_complete_data_and_records_actor_history(): void
    {
        [$format, $gender] = $this->dictionaryItems();
        $owner = $this->user('owner@example.test');
        $group = $this->group($owner);

        $this->actingAs($owner)->from(route('cabinet.groups.show', $group))->followingRedirects()->post(route('cabinet.groups.submit', $group))
            ->assertOk()->assertSee('Группа не отправлена на модерацию')
            ->assertSee('Укажите название группы.')->assertSee('Перейти к редактированию')
            ->assertSee(route('cabinet.groups.edit', $group), false);
        self::assertSame(GroupStatus::Draft, $group->fresh()->status);
        self::assertSame(0, $group->statusHistory()->count());

        $group->update($this->storedPayload($format, $gender));
        $this->actingAs($owner)->post(route('cabinet.groups.submit', $group))->assertSessionDoesntHaveErrors();

        self::assertSame(GroupStatus::Moderation, $group->fresh()->status);
        $this->assertDatabaseHas('gp_group_status_history', [
            'group_id' => $group->getKey(), 'from_status' => 'draft', 'to_status' => 'moderation',
            'actor_id' => $owner->getKey(), 'actor_type' => 'user',
        ]);
    }

    public function test_normal_testing_seed_supports_session_login_edit_and_submit_flow(): void
    {
        $this->seed(DatabaseSeeder::class);
        $email = (string) config('seed.psychologist.email');
        $password = (string) config('seed.psychologist.password');

        $this->post(route('login.store'), ['email' => $email, 'password' => $password])->assertRedirect();
        $this->post(route('cabinet.groups.store'))->assertRedirect();
        $owner = User::query()->where('email', $email)->firstOrFail();
        $group = Group::query()->whereBelongsTo($owner, 'owner')->firstOrFail();
        $format = DictionaryItem::query()->where('code', 'development-test-format')->firstOrFail();
        $gender = DictionaryItem::query()->where('code', 'development-test-gender')->firstOrFail();

        $this->get(route('cabinet.groups.edit', $group))->assertOk()
            ->assertSee('Технический тестовый формат')
            ->assertSee('Техническая тестовая аудитория');
        $this->put(route('cabinet.groups.update', $group), $this->groupPayload($format, $gender))->assertRedirect();
        $this->followingRedirects()->post(route('cabinet.groups.submit', $group))
            ->assertOk()->assertSee('Группа отправлена на модерацию.');

        self::assertSame(GroupStatus::Moderation, $group->fresh()->status);
        self::assertSame(1, $group->statusHistory()->count());
        $this->assertDatabaseHas('gp_group_status_history', [
            'group_id' => $group->getKey(), 'from_status' => 'draft', 'to_status' => 'moderation',
            'actor_type' => 'user', 'actor_id' => $owner->getKey(),
        ]);
        $this->get(route('cabinet.groups.show', $group))->assertOk()->assertSee('На модерации');
    }

    public function test_psychologist_policy_prevents_idor_and_limits_edit_delete(): void
    {
        $owner = $this->user('owner@example.test');
        $intruder = $this->user('intruder@example.test');
        $group = $this->group($owner);

        $this->actingAs($intruder)->get(route('cabinet.groups.show', $group))->assertForbidden();
        $this->actingAs($intruder)->get(route('cabinet.groups.edit', $group))->assertForbidden();
        $this->actingAs($intruder)->put(route('cabinet.groups.update', $group), ['name' => 'Чужое'])->assertForbidden();
        $this->actingAs($intruder)->post(route('cabinet.groups.submit', $group))->assertForbidden();
        $this->actingAs($intruder)->delete(route('cabinet.groups.destroy', $group))->assertForbidden();

        $group->forceFill(['status' => GroupStatus::Moderation])->save();
        $this->actingAs($owner)->get(route('cabinet.groups.edit', $group))->assertForbidden();
        $this->actingAs($owner)->delete(route('cabinet.groups.destroy', $group))->assertForbidden();

        $draft = $this->group($owner);
        $this->actingAs($owner)->delete(route('cabinet.groups.destroy', $draft))->assertRedirect(route('cabinet.groups'));
        self::assertTrue($draft->fresh()->trashed());
    }

    public function test_succeeded_payment_blocks_deletion_and_list_is_owner_scoped_without_n_plus_one(): void
    {
        [$format] = $this->dictionaryItems();
        $owner = $this->user('owner@example.test');
        $other = $this->user('other@example.test');
        $paid = $this->group($owner, ['format_id' => $format->getKey()]);
        Payment::query()->create([
            'owner_id' => $owner->getKey(), 'group_id' => $paid->getKey(), 'type' => 'placement',
            'amount' => 100, 'status' => PaymentStatus::Succeeded,
        ]);
        $this->actingAs($owner)->delete(route('cabinet.groups.destroy', $paid))->assertForbidden();

        for ($index = 0; $index < 24; $index++) {
            $this->group($owner, ['name' => 'Своя '.$index, 'format_id' => $format->getKey()]);
        }
        $this->group($other, ['name' => 'Чужая группа']);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($owner)->get(route('cabinet.groups'))->assertOk()->assertSee('Своя 23')->assertDontSee('Чужая группа');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        self::assertLessThanOrEqual(8, $queryCount);
    }

    /** @return array{DictionaryItem, DictionaryItem} */
    private function dictionaryItems(): array
    {
        $formatDictionary = Dictionary::query()->create(['code' => 'group_format', 'name' => 'Формат']);
        $genderDictionary = Dictionary::query()->create(['code' => 'gender', 'name' => 'Пол']);

        return [
            DictionaryItem::query()->create(['dictionary_id' => $formatDictionary->getKey(), 'code' => 'test-online', 'name' => 'Тестовый онлайн']),
            DictionaryItem::query()->create(['dictionary_id' => $genderDictionary->getKey(), 'code' => 'test-any', 'name' => 'Тестовый любой']),
        ];
    }

    /** @param array<string, mixed> $attributes */
    private function user(string $email, array $attributes = []): User
    {
        return User::query()->create(array_merge(['email' => $email, 'password' => 'secret-password', 'status' => UserStatus::Approved, 'disabled' => false, 'admin' => false, 'free' => false], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    private function group(User $owner, array $attributes = []): Group
    {
        return Group::query()->forceCreate(array_merge(['owner_id' => $owner->getKey(), 'status' => GroupStatus::Draft, 'disabled' => false, 'free' => $owner->free], $attributes));
    }

    /** @return array<string, mixed> */
    private function groupPayload(DictionaryItem $format, DictionaryItem $gender): array
    {
        return ['name' => 'Тестовая группа', 'description' => 'Описание', 'schedule' => 'По вторникам', 'format_id' => $format->getKey(), 'meeting_duration_minutes' => 90, 'participant_count' => 8, 'gender_id' => $gender->getKey(), 'price_per_meeting' => '50,00'];
    }

    /** @return array<string, mixed> */
    private function storedPayload(DictionaryItem $format, DictionaryItem $gender): array
    {
        return array_merge($this->groupPayload($format, $gender), ['price_per_meeting' => 5000]);
    }
}
