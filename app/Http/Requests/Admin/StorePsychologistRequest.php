<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;

final class StorePsychologistRequest extends PsychologistProfileRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        return $this->profileRules() + ['free' => ['required', 'boolean']];
    }
}
