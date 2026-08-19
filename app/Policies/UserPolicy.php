<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->admin;
    }

    public function create(User $actor): bool
    {
        return $actor->admin;
    }

    public function view(User $actor, User $psychologist): bool
    {
        return $actor->admin && ! $psychologist->admin && ! $psychologist->trashed();
    }

    public function viewOwnProfile(User $actor, User $psychologist): bool
    {
        return ! $actor->admin && $actor->is($psychologist) && ! $psychologist->trashed();
    }

    public function update(User $actor, User $psychologist): bool
    {
        return $this->view($actor, $psychologist);
    }

    public function delete(User $actor, User $psychologist): bool
    {
        return $this->view($actor, $psychologist);
    }

    public function manage(User $actor, User $psychologist): bool
    {
        return $this->view($actor, $psychologist);
    }
}
