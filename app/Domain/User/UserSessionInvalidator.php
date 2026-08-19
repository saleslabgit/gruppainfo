<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class UserSessionInvalidator
{
    public function invalidate(User $user): int
    {
        if (config('session.driver') !== 'database') {
            throw new RuntimeException('Per-user session invalidation requires the database session driver.');
        }

        $connection = config('session.connection');
        $table = (string) config('session.table');

        if ($table === '') {
            throw new RuntimeException('The database session table is not configured.');
        }

        return DB::connection(is_string($connection) && $connection !== '' ? $connection : null)
            ->table($table)
            ->where('user_id', $user->getKey())
            ->delete();
    }
}
