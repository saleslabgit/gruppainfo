<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Domain\User\UserStatus;
use App\Http\Requests\GroupDataRequest;
use App\Models\Group;
use Illuminate\Validation\Rule;

final class StoreGroupRequest extends GroupDataRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Group::class) ?? false;
    }

    public function rules(): array
    {
        return ['owner_id' => [
            'required', 'integer', Rule::exists('gp_users', 'id')->where(fn ($query) => $query
                ->where('admin', false)->where('disabled', false)->whereNull('deleted_at')
                ->where('status', UserStatus::Approved->value)),
        ]] + parent::rules();
    }

    protected function complete(): bool
    {
        return false;
    }
}
