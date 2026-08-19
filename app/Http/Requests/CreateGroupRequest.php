<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;

final class CreateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Group::class) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
