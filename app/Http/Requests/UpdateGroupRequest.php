<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class UpdateGroupRequest extends GroupDataRequest
{
    public function authorize(): bool
    {
        $group = $this->routeGroup();

        return $group !== null && ($this->user()?->can('update', $group) ?? false);
    }

    protected function complete(): bool
    {
        return false;
    }
}
