<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\User\UserSessionInvalidator;
use App\Domain\User\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UserSessionInvalidatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_removes_all_target_sessions_and_preserves_unrelated_sessions(): void
    {
        config()->set('session.driver', 'database');

        $target = $this->createUser('target@example.test');
        $other = $this->createUser('other@example.test');

        DB::table((string) config('session.table'))->insert([
            $this->sessionRow('target-one', $target->getKey()),
            $this->sessionRow('target-two', $target->getKey()),
            $this->sessionRow('other-one', $other->getKey()),
        ]);

        $deleted = app(UserSessionInvalidator::class)->invalidate($target);

        self::assertSame(2, $deleted);
        $this->assertDatabaseMissing('sessions', ['id' => 'target-one']);
        $this->assertDatabaseMissing('sessions', ['id' => 'target-two']);
        $this->assertDatabaseHas('sessions', ['id' => 'other-one', 'user_id' => $other->getKey()]);
        self::assertSame(0, app(UserSessionInvalidator::class)->invalidate($target));
    }

    private function createUser(string $email): User
    {
        return User::query()->create([
            'email' => $email,
            'password' => 'secret-password',
            'status' => UserStatus::Approved,
            'disabled' => false,
            'admin' => false,
            'free' => false,
        ]);
    }

    /**
     * @return array<string, int|string|null>
     */
    private function sessionRow(string $id, int|string $userId): array
    {
        return [
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => time(),
        ];
    }
}
