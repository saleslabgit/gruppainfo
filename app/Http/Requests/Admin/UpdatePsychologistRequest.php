<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;

final class UpdatePsychologistRequest extends PsychologistProfileRequest
{
    public function authorize(): bool
    {
        $psychologist = $this->route('psychologist');

        return $psychologist instanceof User && ($this->user()?->can('update', $psychologist) ?? false);
    }

    public function rules(): array
    {
        $psychologist = $this->route('psychologist');

        return $this->profileRules($psychologist instanceof User ? $psychologist : null);
    }
}
