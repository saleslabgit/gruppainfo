<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GroupApplication extends Model
{
    protected $table = 'gp_group_applications';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['processed_at' => 'immutable_datetime'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
