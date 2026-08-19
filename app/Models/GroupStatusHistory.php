<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Group\GroupStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property GroupStatus|null $from_status
 * @property GroupStatus $to_status
 * @property string $actor_type
 * @property string|null $comment
 * @property User|null $actor
 */
final class GroupStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'gp_group_status_history';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'from_status' => GroupStatus::class,
            'to_status' => GroupStatus::class,
            'created_at' => 'immutable_datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
