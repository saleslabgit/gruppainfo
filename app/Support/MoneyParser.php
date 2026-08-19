<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class MoneyParser
{
    public static function toMinorUnits(string $value): int
    {
        $normalized = str_replace(',', '.', trim($value));

        if (preg_match('/^(0|[1-9]\d*)(?:\.(\d{1,2}))?$/', $normalized, $matches) !== 1) {
            throw new InvalidArgumentException('Некорректная денежная сумма.');
        }

        $minor = $matches[1].str_pad($matches[2] ?? '', 2, '0');
        $minor = ltrim($minor, '0') ?: '0';

        if (strlen($minor) > strlen((string) PHP_INT_MAX)
            || (strlen($minor) === strlen((string) PHP_INT_MAX) && strcmp($minor, (string) PHP_INT_MAX) > 0)) {
            throw new InvalidArgumentException('Денежная сумма слишком велика.');
        }

        return (int) $minor;
    }

    public static function fromMinorUnits(?int $value): string
    {
        if ($value === null) {
            return '';
        }

        return intdiv($value, 100).','.str_pad((string) ($value % 100), 2, '0', STR_PAD_LEFT);
    }
}
