<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;

final class CleanupGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = $this->route('group');

        return $group instanceof Group && ($this->user()?->can('cleanup', $group) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
