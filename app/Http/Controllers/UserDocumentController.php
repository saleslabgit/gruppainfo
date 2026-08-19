<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserDocument;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class UserDocumentController extends Controller
{
    public function view(UserDocument $document): StreamedResponse
    {
        return $this->response($document, 'inline');
    }

    public function download(UserDocument $document): StreamedResponse
    {
        return $this->response($document, 'attachment');
    }

    private function response(UserDocument $document, string $disposition): StreamedResponse
    {
        Gate::authorize('view', $document);
        $disk = Storage::disk((string) config('documents.disk'));
        abort_unless($disk->exists($document->path), 404);

        return $disk->response(
            $document->path,
            $document->original_name,
            ['Content-Type' => $document->mime_type, 'X-Content-Type-Options' => 'nosniff'],
            $disposition,
        );
    }
}
