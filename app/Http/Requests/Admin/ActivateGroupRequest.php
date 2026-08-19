<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;

final class ActivateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = $this->route('group');

        return $group instanceof Group && ($this->user()?->can('moderate', $group) ?? false);
    }

    public function rules(): array
    {
        return ['external_catalog_id' => ['nullable', 'string', 'max:255']];
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('external_catalog_id')) {
            $value = trim((string) $this->input('external_catalog_id'));
            $this->merge(['external_catalog_id' => $value === '' ? null : $value]);
        }
    }
}
