<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class MoneyFormatter
{
    public static function format(int $minorUnits, string $currency = 'BYN'): string
    {
        if ($minorUnits < 0) {
            throw new InvalidArgumentException('Money amount must not be negative.');
        }

        if (preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw new InvalidArgumentException('Currency must be a three-letter uppercase code.');
        }

        $majorUnits = (string) intdiv($minorUnits, 100);
        $groupedMajorUnits = preg_replace('/\B(?=(\d{3})+(?!\d))/', ' ', $majorUnits);

        if ($groupedMajorUnits === null) {
            throw new InvalidArgumentException('Money amount could not be formatted.');
        }

        $minorPart = str_pad((string) ($minorUnits % 100), 2, '0', STR_PAD_LEFT);

        return $groupedMajorUnits.','.$minorPart.' '.$currency;
    }
}
