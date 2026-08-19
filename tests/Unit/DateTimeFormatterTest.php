<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\DateTimeFormatter;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class DateTimeFormatterTest extends TestCase
{
    public function test_it_displays_a_utc_value_in_europe_minsk(): void
    {
        $utcDateTime = CarbonImmutable::parse('2026-01-15 10:30:00', 'UTC');

        self::assertSame('15.01.2026 13:30', DateTimeFormatter::format($utcDateTime));
    }
}
