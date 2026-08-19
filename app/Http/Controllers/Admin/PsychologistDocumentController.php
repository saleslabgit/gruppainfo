<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\User\UserDocumentService;
use App\Domain\User\UserDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePsychologistDocumentRequest;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class PsychologistDocumentController extends Controller
{
    public function store(
        StorePsychologistDocumentRequest $request,
        User $psychologist,
        UserDocumentService $service,
    ): RedirectResponse {
        $service->store(
            $psychologist,
            UserDocumentType::from((string) $request->validated('type')),
            $request->file('document'),
        );

        return back()->with('status', 'Документ загружен.');
    }

    public function destroy(User $psychologist, UserDocument $document, UserDocumentService $service): RedirectResponse
    {
        abort_unless($document->user_id === $psychologist->getKey(), 404);
        Gate::authorize('delete', $document);
        $service->delete($document);

        return back()->with('status', 'Документ удалён.');
    }
}
