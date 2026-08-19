<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Group\GroupStatus;
use App\Domain\Payment\PaymentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property GroupStatus $status
 * @property User $owner
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $expiry_warning_sent_at
 */
final class Group extends Model
{
    use SoftDeletes;

    protected $table = 'gp_groups';

    protected $guarded = ['id', 'public_uuid', 'accept'];

    protected static function booted(): void
    {
        self::creating(function (self $group): void {
            if (! is_string($group->getAttribute('public_uuid')) || $group->getAttribute('public_uuid') === '') {
                $group->public_uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => GroupStatus::class,
            'disabled' => 'boolean',
            'free' => 'boolean',
            'price_per_meeting' => 'integer',
            'published_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'expiry_warning_sent_at' => 'immutable_datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id')->withTrashed();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(GroupApplication::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function successfulNonRefundedPayments(): HasMany
    {
        return $this->payments()->where('status', PaymentStatus::Succeeded->value);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(GroupStatusHistory::class);
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(DictionaryItem::class, 'format_id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(DictionaryItem::class, 'gender_id');
    }
}
