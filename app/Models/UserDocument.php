<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\User\UserDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property UserDocumentType $type
 * @property int $user_id
 * @property User $user
 */
final class UserDocument extends Model
{
    protected $table = 'gp_user_documents';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => UserDocumentType::class,
            'size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
