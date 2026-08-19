<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\User\UserStatus;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PsychologistDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config()->set('documents.disk', 'local');
    }

    public function test_admin_uploads_private_document_with_content_metadata_and_random_path(): void
    {
        $admin = $this->user('admin@example.test', admin: true);
        $psychologist = $this->user('psychologist@example.test');
        $file = UploadedFile::fake()->createWithContent(
            'portrait.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nGQAAAAASUVORK5CYII='),
        );

        $this->actingAs($admin)->post(route('admin.psychologists.documents.store', $psychologist), [
            'type' => 'diploma',
            'document' => $file,
        ])->assertSessionHas('status');

        $document = UserDocument::query()->firstOrFail();
        self::assertSame('diploma', $document->type->value);
        self::assertSame('portrait.png', $document->original_name);
        self::assertSame('image/png', $document->mime_type);
        self::assertGreaterThan(0, $document->size);
        self::assertStringNotContainsString('portrait.png', $document->path);
        self::assertStringStartsWith("psychologists/{$psychologist->getKey()}/documents/", $document->path);
        Storage::disk('local')->assertExists($document->path);

        $this->actingAs($admin)->get(route('admin.psychologists.show', $psychologist))
            ->assertOk()
            ->assertSee('portrait.png')
            ->assertDontSee($document->path);
    }

    public function test_unsupported_spoofed_mime_and_configured_size_limit_are_rejected(): void
    {
        $admin = $this->user('admin@example.test', admin: true);
        $psychologist = $this->user('psychologist@example.test');

        $this->actingAs($admin)->post(route('admin.psychologists.documents.store', $psychologist), [
            'type' => 'certificate',
            'document' => UploadedFile::fake()->createWithContent('spoofed.jpg', 'plain text'),
        ])->assertSessionHasErrors('document');

        config()->set('documents.max_upload_kb', 1);
        $this->actingAs($admin)->post(route('admin.psychologists.documents.store', $psychologist), [
            'type' => 'certificate',
            'document' => UploadedFile::fake()->createWithContent('large.pdf', "%PDF-1.4\n".str_repeat('0', 2048)),
        ])->assertSessionHasErrors('document');

        self::assertSame(0, UserDocument::query()->count());
    }

    public function test_admin_and_owner_can_receive_private_bytes_but_another_psychologist_cannot(): void
    {
        $admin = $this->user('admin@example.test', admin: true);
        $owner = $this->user('owner@example.test');
        $other = $this->user('other@example.test');
        Storage::disk('local')->put('psychologists/owner/document.pdf', 'private-bytes');
        $document = UserDocument::query()->create([
            'user_id' => $owner->getKey(),
            'type' => 'diploma',
            'path' => 'psychologists/owner/document.pdf',
            'original_name' => 'Диплом.pdf',
            'mime_type' => 'application/pdf',
            'size' => 13,
        ]);

        $adminResponse = $this->actingAs($admin)->get(route('documents.view', $document));
        $adminResponse->assertOk()->assertHeader('content-type', 'application/pdf');
        self::assertSame('private-bytes', $adminResponse->streamedContent());

        $ownerResponse = $this->actingAs($owner)->get(route('documents.download', $document));
        $ownerResponse->assertOk();
        self::assertStringContainsString('attachment', (string) $ownerResponse->headers->get('content-disposition'));
        self::assertSame('private-bytes', $ownerResponse->streamedContent());

        $this->actingAs($other)->get(route('documents.view', $document))->assertForbidden();
        self::assertContains($this->get('/storage/'.$document->path)->getStatusCode(), [403, 404]);
    }

    public function test_admin_delete_removes_file_and_row_and_nested_id_tampering_is_rejected(): void
    {
        $admin = $this->user('admin@example.test', admin: true);
        $first = $this->user('first@example.test');
        $second = $this->user('second@example.test');
        Storage::disk('local')->put('psychologists/second/document.png', 'bytes');
        $document = UserDocument::query()->create([
            'user_id' => $second->getKey(),
            'type' => 'license',
            'path' => 'psychologists/second/document.png',
            'original_name' => 'license.png',
            'mime_type' => 'image/png',
            'size' => 5,
        ]);

        $this->actingAs($admin)->delete(route('admin.psychologists.documents.destroy', [$first, $document]))->assertNotFound();
        Storage::disk('local')->assertExists($document->path);
        $this->assertDatabaseHas('gp_user_documents', ['id' => $document->getKey()]);

        $this->actingAs($admin)->delete(route('admin.psychologists.documents.destroy', [$second, $document]))->assertSessionHas('status');
        Storage::disk('local')->assertMissing($document->path);
        $this->assertDatabaseMissing('gp_user_documents', ['id' => $document->getKey()]);
        $this->assertDatabaseHas('gp_users', ['id' => $second->getKey(), 'deleted_at' => null]);
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
}
