<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Models\User;
use App\Models\UserDocument;
use App\Support\UploadedDocumentMime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final class UserDocumentService
{
    public function store(User $psychologist, UserDocumentType $type, UploadedFile $file): UserDocument
    {
        $disk = Storage::disk((string) config('documents.disk'));
        $path = $file->store("psychologists/{$psychologist->getKey()}/documents", (string) config('documents.disk'));

        if (! is_string($path)) {
            throw new RuntimeException('Не удалось сохранить документ.');
        }

        try {
            return UserDocument::query()->create([
                'user_id' => $psychologist->getKey(),
                'type' => $type,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => UploadedDocumentMime::detect($file),
                'size' => $file->getSize(),
            ]);
        } catch (Throwable $exception) {
            $disk->delete($path);

            throw $exception;
        }
    }

    public function delete(UserDocument $document): void
    {
        $disk = Storage::disk((string) config('documents.disk'));

        if ($disk->exists($document->path) && ! $disk->delete($document->path)) {
            throw new RuntimeException('Не удалось удалить файл документа.');
        }

        $document->delete();
    }
}
