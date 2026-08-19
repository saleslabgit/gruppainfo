<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\User\UserStatus;
use App\Models\Dictionary;
use App\Models\DictionaryItem;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PsychologistCabinetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config()->set('documents.disk', 'local');
    }

    public function test_guest_and_administrator_cannot_access_psychologist_cabinet_routes(): void
    {
        $admin = $this->user('admin@example.test', admin: true);

        foreach (['/cabinet', '/cabinet/groups', '/cabinet/profile'] as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }

        foreach (['/cabinet', '/cabinet/groups', '/cabinet/profile'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertForbidden();
        }
    }

    public function test_cabinet_entry_redirects_to_truthful_groups_empty_state(): void
    {
        $psychologist = $this->user('psychologist@example.test');

        $this->actingAs($psychologist)->get('/cabinet')
            ->assertRedirect(route('cabinet.groups'));

        $response = $this->actingAs($psychologist)->get(route('cabinet.groups'))
            ->assertOk()
            ->assertSee('Мои группы')
            ->assertSee('Пока нет групп')
            ->assertSee('Добавленные группы появятся в этом разделе.')
            ->assertSee('Мои данные')
            ->assertSee('ui-nav-item is-active', false)
            ->assertSee(route('logout'), false);

        $html = $response->getContent();
        self::assertStringNotContainsString('Добавить группу', $html);
        self::assertStringNotContainsString('/cabinet/groups/create', $html);
        self::assertStringNotContainsString('Редактировать', $html);
    }

    public function test_profile_is_read_only_and_uses_only_authenticated_psychologist_data(): void
    {
        $educationDictionary = Dictionary::query()->create([
            'code' => 'education_type',
            'name' => 'Тип образования',
        ]);
        $educationType = DictionaryItem::query()->create([
            'dictionary_id' => $educationDictionary->getKey(),
            'code' => 'higher',
            'name' => 'Высшее психологическое',
        ]);
        $owner = $this->user('owner@example.test', [
            'last_name' => 'Иванова',
            'first_name' => 'Ирина',
            'middle_name' => null,
            'phone' => '+375291234567',
            'education_type_id' => $educationType->getKey(),
            'other_education' => null,
            'modality' => 'Гештальт-терапия',
            'training_center' => 'Учебный центр владельца',
            'graduation_year' => 2020,
            'training_hours' => 500,
            'license_number' => 'OWNER-LICENSE',
            'license_expires_at' => '2027-08-19',
            'group_leading_experience' => 'Пять лет',
            'groups_held_count' => 12,
            'documents_truth_confirmed' => true,
            'education_compliance_confirmed' => false,
            'ready_to_host_webinar' => null,
            'personal_data_consent_at' => '2026-08-19 12:00:00',
            'personal_data_consent_version' => 'consent-v1',
        ]);
        $other = $this->user('other@example.test', [
            'first_name' => 'ЧужойУникальныйМаркер',
            'license_number' => 'OTHER-LICENSE',
        ]);

        $response = $this->actingAs($owner)->get(route('cabinet.profile', [
            'user_id' => $other->getKey(),
            'psychologist_id' => $other->getKey(),
        ]))->assertOk();

        $response
            ->assertSee('Мои данные')
            ->assertSee('Иванова')
            ->assertSee('owner@example.test')
            ->assertSee('Высшее психологическое')
            ->assertSee('Гештальт-терапия')
            ->assertSee('OWNER-LICENSE')
            ->assertSee('19.08.2027')
            ->assertSee('19.08.2026 15:00')
            ->assertSee('consent-v1')
            ->assertSee('Не указано')
            ->assertSeeInOrder(['Подлинность документов', 'Да', 'Соответствие образования', 'Нет', 'Готовность к вебинару / эфиру', 'Не указано'])
            ->assertDontSee('ЧужойУникальныйМаркер')
            ->assertDontSee('OTHER-LICENSE');

        $html = $response->getContent();
        foreach (['Редактировать', 'Сохранить', 'Загрузить', 'Удалить', 'Сменить тариф', 'Отключить доступ', 'Пароль', 'История действий'] as $forbiddenText) {
            self::assertStringNotContainsString($forbiddenText, $html);
        }
        foreach (['admin', 'disabled', 'accept', 'active_email', 'remember_token'] as $forbiddenField) {
            self::assertStringNotContainsString('>'.$forbiddenField.'<', $html);
        }
    }

    public function test_profile_lists_only_own_private_documents_without_exposing_paths(): void
    {
        $owner = $this->user('owner@example.test');
        $other = $this->user('other@example.test');
        Storage::disk('local')->put('psychologists/owner/owner.pdf', 'owner-private-bytes');
        Storage::disk('local')->put('psychologists/other/secret.pdf', 'other-private-bytes');
        $ownerDocument = $this->document($owner, 'psychologists/owner/owner.pdf', 'Диплом владельца.pdf');
        $otherDocument = $this->document($other, 'psychologists/other/secret.pdf', 'Чужой секрет.pdf');

        $response = $this->actingAs($owner)->get(route('cabinet.profile'))
            ->assertOk()
            ->assertSee('Диплом владельца.pdf')
            ->assertSee('Диплом · 2,0 КБ')
            ->assertSee(route('documents.view', $ownerDocument), false)
            ->assertSee(route('documents.download', $ownerDocument), false)
            ->assertDontSee('Чужой секрет.pdf')
            ->assertDontSee($ownerDocument->path)
            ->assertDontSee($otherDocument->path)
            ->assertDontSee('/storage/');

        self::assertStringNotContainsString('Загрузить', $response->getContent());
        self::assertStringNotContainsString('Удалить', $response->getContent());

        $ownView = $this->actingAs($owner)->get(route('documents.view', $ownerDocument));
        $ownView->assertOk();
        self::assertSame('owner-private-bytes', $ownView->streamedContent());

        $ownDownload = $this->actingAs($owner)->get(route('documents.download', $ownerDocument));
        $ownDownload->assertOk();
        self::assertSame('owner-private-bytes', $ownDownload->streamedContent());

        $this->actingAs($owner)->get(route('documents.view', $otherDocument))->assertForbidden();
        $this->actingAs($owner)->get(route('documents.download', $otherDocument))->assertForbidden();
    }

    public function test_profile_without_documents_uses_shared_empty_state(): void
    {
        $psychologist = $this->user('psychologist@example.test');

        $this->actingAs($psychologist)->get(route('cabinet.profile'))
            ->assertOk()
            ->assertSee('ui-empty-state', false)
            ->assertSee('Документов пока нет')
            ->assertSee('Загруженные документы появятся в этом разделе.');
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

    private function document(User $owner, string $path, string $name): UserDocument
    {
        return UserDocument::query()->create([
            'user_id' => $owner->getKey(),
            'type' => 'diploma',
            'path' => $path,
            'original_name' => $name,
            'mime_type' => 'application/pdf',
            'size' => 2048,
        ]);
    }
}
