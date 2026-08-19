<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\User\UserStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property UserStatus $status
 * @property bool $disabled
 * @property bool $free
 * @property bool $admin
 * @property CarbonImmutable|null $personal_data_consent_at
 */
final class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    protected $table = 'gp_users';

    protected $guarded = ['id', 'active_email', 'accept'];

    protected $hidden = ['password', 'remember_token', 'active_email'];

    protected function casts(): array
    {
        return [
            'license_expires_at' => 'date',
            'documents_truth_confirmed' => 'boolean',
            'education_compliance_confirmed' => 'boolean',
            'ready_to_host_webinar' => 'boolean',
            'personal_data_consent_at' => 'immutable_datetime',
            'status' => UserStatus::class,
            'disabled' => 'boolean',
            'free' => 'boolean',
            'admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function educationType(): BelongsTo
    {
        return $this->belongsTo(DictionaryItem::class, 'education_type_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }

    public function actionHistory(): HasMany
    {
        return $this->hasMany(UserActionHistory::class, 'target_user_id');
    }

    public function fullName(): string
    {
        $parts = array_filter([$this->last_name, $this->first_name, $this->middle_name]);

        return $parts === [] ? 'Не указано' : implode(' ', $parts);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'owner_id');
    }
}
