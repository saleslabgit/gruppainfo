<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Settings\SettingType;
use Illuminate\Database\Eloquent\Model;

/**
 * @property SettingType $type
 */
final class Setting extends Model
{
    protected $table = 'gp_settings';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['type' => SettingType::class];
    }
}
