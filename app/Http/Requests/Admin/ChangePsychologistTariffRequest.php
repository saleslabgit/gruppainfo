<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class ChangePsychologistTariffRequest extends FormRequest
{
    public function authorize(): bool
    {
        $psychologist = $this->route('psychologist');

        return $psychologist instanceof User && ($this->user()?->can('manage', $psychologist) ?? false);
    }

    public function rules(): array
    {
        return ['free' => ['required', 'boolean']];
    }
}
