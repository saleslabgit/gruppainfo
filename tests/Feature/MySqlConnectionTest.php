<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MySqlConnectionTest extends TestCase
{
    public function test_the_test_suite_uses_the_isolated_mysql_database(): void
    {
        self::assertSame('mysql', DB::connection()->getDriverName());
        self::assertSame('gruppainfo_test', DB::connection()->getDatabaseName());
        self::assertSame(1, DB::selectOne('SELECT 1 AS connected')->connected);
    }
}
