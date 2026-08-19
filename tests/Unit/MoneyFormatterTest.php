<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\MoneyFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class MoneyFormatterTest extends TestCase
{
    /**
     * @return array<string, array{int, string}>
     */
    public static function amounts(): array
    {
        return [
            'zero' => [0, '0,00 BYN'],
            'minor units' => [5, '0,05 BYN'],
            'whole and fractional units' => [12345, '123,45 BYN'],
            'grouped amount' => [123456789, '1 234 567,89 BYN'],
        ];
    }

    #[DataProvider('amounts')]
    public function test_it_formats_integer_minor_units(int $minorUnits, string $expected): void
    {
        self::assertSame($expected, MoneyFormatter::format($minorUnits));
    }

    public function test_it_accepts_only_integer_minor_units(): void
    {
        $parameter = (new ReflectionMethod(MoneyFormatter::class, 'format'))->getParameters()[0];

        self::assertSame('int', (string) $parameter->getType());
    }
}
