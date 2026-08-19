<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

final class DateTimeFormatter
{
    public static function format(DateTimeInterface $dateTime, string $format = 'd.m.Y H:i'): string
    {
        return CarbonImmutable::instance($dateTime)
            ->setTimezone((string) config('app.display_timezone'))
            ->format($format);
    }
}
