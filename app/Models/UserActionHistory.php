<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\User\UserAction;
use App\Domain\User\UserStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserActionHistory extends Model
{
    public $timestamps = false;

    protected $table = 'gp_user_action_history';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'action' => UserAction::class,
            'from_status' => UserStatus::class,
            'to_status' => UserStatus::class,
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id')->withTrashed();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id')->withTrashed();
    }
}
