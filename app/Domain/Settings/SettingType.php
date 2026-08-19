<?php

declare(strict_types=1);

namespace App\Domain\Settings;

enum SettingType: string
{
    case Integer = 'integer';
    case String = 'string';
    case Boolean = 'boolean';
}
