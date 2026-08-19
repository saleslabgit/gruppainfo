<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserDocument;

final class UserDocumentPolicy
{
    public function view(User $actor, UserDocument $document): bool
    {
        return $actor->admin || $document->user_id === $actor->getKey();
    }

    public function create(User $actor, User $psychologist): bool
    {
        return $actor->admin && ! $psychologist->admin && ! $psychologist->trashed();
    }

    public function delete(User $actor, UserDocument $document): bool
    {
        return $actor->admin && ! $document->user->admin && ! $document->user->trashed();
    }
}
