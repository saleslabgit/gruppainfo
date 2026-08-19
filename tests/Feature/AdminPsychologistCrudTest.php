<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Group\GroupStatus;
use App\Domain\User\UserAction;
use App\Domain\User\UserStatus;
use App\Models\Group;
use App\Models\User;
use App\Models\UserActionHistory;
use App\Models\UserDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AdminPsychologistCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_boundary_and_list_scope_are_enforced(): void
    {
        $admin = $this->user('admin@example.test', admin: true);
        $psychologist = $this->user('visible@example.test', ['first_name' => 'Видимый']);
        $otherAdmin = $this->user('other-admin@example.test', admin: true);
        $deleted = $this->user('deleted@example.test', ['first_name' => 'Удалённый']);
        $deleted->delete();

        $this->get('/admin/psychologists')->assertRedirect('/login');
        $this->actingAs($psychologist)->get('/admin/psychologists')->assertForbidden();
        $this->actingAs($admin)->get('/admin/psychologists')
            ->assertOk()
            ->assertSee('visible@example.test')
            ->assertDontSee('other-admin@example.test')
            ->assertDontSee('deleted@example.test');

        $this->actingAs($admin)->get(route('admin.psychologists.show', $otherAdmin))->assertForbidden();
    }

    public function test_search_filters_pagination_and_query_count_are_bounded(): void
    {
        $admin = $this->user('admin@example.test', admin: true);
        $target = $this->user('target@example.test', [
            'first_name' => 'Марина',
            'last_name' => 'Тестовая',
            'phone' => '+375291112233',
            'status' => UserStatus::Pending,
            'free' => true,
        ]);
        $this->user('other@example.test', ['first_name' => 'Другая', 'status' => UserStatus::Approved, 'free' => false]);

        $this->actingAs($admin)->get('/admin/psychologists?search=291112233&status=pending&tariff=free')
            ->assertOk()
            ->assertSee($target->email)
            ->assertDontSee('other@example.test');

        for ($index = 0; $index < 24; $index++) {
            $this->user("page-{$index}@example.test", ['status' => UserStatus::Pending, 'free' => true]);
        }

        $this->actingAs($admin)->get('/admin/psychologists?status=pending&tariff=free')
            ->assertOk()
            ->assertSee('status=pending&amp;tariff=free&amp;page=2', false);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/psychologists')->assertOk();
        $manyRowsQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        self::assertLessThanOrEqual(6, $manyRowsQueryCount);
    }

    public function test_admin_creates_and_edits_only_approved_profile_fields(): void
    {
        $admin = $this->user('admin@example.test', admin: true);
        $payload = $this->profilePayload('new@example.test') + [
            'free' => '1',
            'admin' => '1',
            'status' => UserStatus::Approved->value,
            'disabled' => '1',
            'password' => 'injected-password',
            'accept' => '1',
        ];

        $response = $this->actingAs($admin)->post(route('admin.psychologists.store'), $payload);
        $psychologist = User::query()->where('email', 'new@example.test')->firstOrFail();

        $response->assertRedirect(route('admin.psychologists.show', $psychologist));
        self::assertFalse($psychologist->admin);
        self::assertSame(UserStatus::Pending, $psychologist->status);
        self::assertFalse($psychologist->disabled);
        self::assertTrue($psychologist->free);
        self::assertNull($psychologist->password);
        self::assertFalse((bool) $psychologist->accept);
        self::assertSame('09:00', $psychologist->personal_data_consent_at?->format('H:i'));
        $this->assertDatabaseHas('gp_user_action_history', [
            'target_user_id' => $psychologist->getKey(),
            'actor_user_id' => $admin->getKey(),
            'action' => UserAction::Created->value,
        ]);

        $this->actingAs($admin)->get(route('admin.psychologists.show', $psychologist))
            ->assertOk()
            ->assertSee('new@example.test')
            ->assertSee('Тестовая модальность')
            ->assertSee('v1');

        $this->actingAs($admin)->put(route('admin.psychologists.update', $psychologist), $this->profilePayload('changed@example.test') + [
            'status' => UserStatus::Approved->value,
            'disabled' => '1',
            'free' => '0',
            'admin' => '1',
            'password' => 'changed-password',
            'accept' => '1',
        ])->assertRedirect(route('admin.psychologists.show', $psychologist));

        $psychologist->refresh();
        self::assertSame('changed@example.test', $psychologist->email);
        self::assertSame(UserStatus::Pending, $psychologist->status);
        self::assertFalse($psychologist->disabled);
        self::assertTrue($psychologist->free);
        self::assertFalse($psychologist->admin);
        self::assertNull($psychologist->password);
        self::assertFalse((bool) $psychologist->accept);
    }

    public function test_active_email_is_unique_and_soft_deleted_email_can_be_reused(): void
    {
        $admin = $this->user('admin@example.test', admin: true);
        $active = $this->user('duplicate@example.test');

        $this->actingAs($admin)->post(route('admin.psychologists.store'), $this->profilePayload($active->email) + ['free' => '0'])
            ->assertSessionHasErrors('email');

        $active->delete();

        $this->actingAs($admin)->post(route('admin.psychologists.store'), $this->profilePayload('duplicate@example.test') + ['free' => '0'])
            ->assertSessionDoesntHaveErrors();

        self::assertSame(1, User::query()->where('email', 'duplicate@example.test')->count());
        self::assertSame(2, User::withTrashed()->where('email', 'duplicate@example.test')->count());
    }

    public function test_status_tariff_access_and_delete_actions_are_audited_and_revoke_only_target_sessions(): void
    {
        config()->set('session.driver', 'database');
        $admin = $this->user('admin@example.test', admin: true);
        $target = $this->user('target@example.test', ['status' => UserStatus::Pending]);
        $other = $this->user('other@example.test');
        DB::table('sessions')->insert([
            $this->sessionRow('target-session', $target),
            $this->sessionRow('other-session', $other),
        ]);

        $this->actingAs($admin)->post(route('admin.psychologists.approve', $target))->assertSessionHas('status');
        self::assertSame(UserStatus::Approved, $target->fresh()->status);
        $this->assertDatabaseHas('gp_user_action_history', [
            'target_user_id' => $target->getKey(),
            'actor_user_id' => $admin->getKey(),
            'action' => UserAction::Approved->value,
            'from_status' => UserStatus::Pending->value,
            'to_status' => UserStatus::Approved->value,
        ]);
        $this->actingAs($admin)->post(route('admin.psychologists.reject', $target))->assertSessionHas('error');
        self::assertSame(UserStatus::Approved, $target->fresh()->status);

        $group = Group::query()->create([
            'owner_id' => $target->getKey(),
            'status' => GroupStatus::Draft,
            'free' => false,
        ]);
        $this->actingAs($admin)->patch(route('admin.psychologists.tariff', $target), ['free' => true])->assertSessionHas('status');
        self::assertTrue($target->fresh()->free);
        self::assertFalse($group->fresh()->free);
        $tariffAudit = UserActionHistory::query()->where('action', UserAction::TariffChanged->value)->firstOrFail();
        self::assertSame(['after' => true, 'before' => false], $tariffAudit->metadata);

        $this->actingAs($admin)->post(route('admin.psychologists.disable', $target))->assertSessionHas('status');
        self::assertTrue($target->fresh()->disabled);
        $this->assertDatabaseMissing('sessions', ['id' => 'target-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'other-session']);
        $this->assertDatabaseHas('gp_user_action_history', ['action' => UserAction::Disabled->value, 'actor_user_id' => $admin->getKey()]);

        $this->actingAs($admin)->post(route('admin.psychologists.enable', $target))->assertSessionHas('status');
        self::assertFalse($target->fresh()->disabled);
        $this->assertDatabaseMissing('sessions', ['user_id' => $target->getKey()]);
        $this->assertDatabaseHas('gp_user_action_history', ['action' => UserAction::Enabled->value, 'actor_user_id' => $admin->getKey()]);

        UserDocument::query()->create([
            'user_id' => $target->getKey(),
            'type' => 'diploma',
            'path' => 'retained/document.pdf',
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10,
        ]);
        DB::table('sessions')->insert($this->sessionRow('delete-session', $target));
        $this->actingAs($admin)->delete(route('admin.psychologists.destroy', $target))->assertRedirect(route('admin.psychologists.index'));
        $this->assertSoftDeleted('gp_users', ['id' => $target->getKey()]);
        $this->assertDatabaseHas('gp_user_documents', ['user_id' => $target->getKey(), 'path' => 'retained/document.pdf']);
        $this->assertDatabaseMissing('sessions', ['id' => 'delete-session']);
        $this->assertDatabaseHas('gp_user_action_history', ['target_user_id' => $target->getKey(), 'action' => UserAction::Deleted->value]);

        $this->actingAs($admin)->post(route('admin.psychologists.store'), $this->profilePayload('target@example.test') + ['free' => '0'])
            ->assertSessionDoesntHaveErrors();
        self::assertSame(1, User::query()->where('email', 'target@example.test')->count());

        $auditPayload = UserActionHistory::query()->where('target_user_id', $target->getKey())->get()->toJson();
        self::assertStringNotContainsString('password', $auditPayload);
        self::assertStringNotContainsString('session', $auditPayload);
        self::assertStringNotContainsString('private-bytes', $auditPayload);
    }

    public function test_reject_revokes_target_sessions_and_records_actor(): void
    {
        config()->set('session.driver', 'database');
        $admin = $this->user('admin@example.test', admin: true);
        $target = $this->user('target@example.test', ['status' => UserStatus::Pending]);
        DB::table('sessions')->insert($this->sessionRow('rejected-session', $target));

        $this->actingAs($admin)->post(route('admin.psychologists.reject', $target))->assertSessionHas('status');

        self::assertSame(UserStatus::Rejected, $target->fresh()->status);
        $this->assertDatabaseMissing('sessions', ['id' => 'rejected-session']);
        $this->assertDatabaseHas('gp_user_action_history', [
            'target_user_id' => $target->getKey(),
            'actor_user_id' => $admin->getKey(),
            'action' => UserAction::Rejected->value,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function user(string $email, array $attributes = [], bool $admin = false): User
    {
        return User::query()->create(array_merge([
            'email' => $email,
            'password' => 'secret-password',
            'status' => UserStatus::Approved,
            'disabled' => false,
            'admin' => $admin,
            'free' => false,
        ], $attributes));
    }

    /** @return array<string, mixed> */
    private function profilePayload(string $email): array
    {
        return [
            'last_name' => 'Иванова',
            'first_name' => 'Ирина',
            'middle_name' => 'Петровна',
            'phone' => '+375291234567',
            'email' => $email,
            'education_type_id' => null,
            'other_education' => 'Другое образование',
            'modality' => 'Тестовая модальность',
            'training_center' => 'Учебный центр',
            'graduation_year' => 2020,
            'training_hours' => 500,
            'license_number' => 'LICENSE-1',
            'license_expires_at' => '2027-08-19',
            'group_leading_experience' => 'Пять лет',
            'groups_held_count' => 12,
            'documents_truth_confirmed' => '1',
            'education_compliance_confirmed' => '0',
            'ready_to_host_webinar' => null,
            'personal_data_consent_at' => '2026-08-19 12:00:00',
            'personal_data_consent_version' => 'v1',
        ];
    }

    /** @return array<string, int|string|null> */
    private function sessionRow(string $id, User $user): array
    {
        return [
            'id' => $id,
            'user_id' => $user->getKey(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => time(),
        ];
    }
}
