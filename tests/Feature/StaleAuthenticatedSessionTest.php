<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\User\UserStatus;
use App\Http\Middleware\RevokeStaleAuthenticatedSession;
use App\Models\User;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class StaleAuthenticatedSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deleted_user_session_stays_revoked_after_restore_until_fresh_login(): void
    {
        $user = $this->createUser('soft-deleted@example.test');

        $this->post('/login', $this->credentials($user))->assertRedirect('/cabinet');
        $protectedResponse = $this->get('/cabinet')->assertRedirect(route('cabinet.groups'));

        $sessionId = $this->sessionIdFrom($protectedResponse);
        $authSessionKey = $this->authSessionKey();
        $user->delete();

        $response = $this->get('/cabinet')
            ->assertRedirect('/login')
            ->assertSessionHas('access_message', 'Доступ к учётной записи недоступен.');

        $response->assertSessionMissing($authSessionKey);
        $this->assertGuest();
        self::assertNotSame($sessionId, $this->sessionIdFrom($response));

        $user->restore();

        $this->get('/cabinet')->assertRedirect('/login');
        $this->assertGuest();

        $this->post('/login', $this->credentials($user))->assertRedirect('/cabinet');
        $this->get('/cabinet')->assertRedirect(route('cabinet.groups'));
    }

    public function test_session_for_missing_user_is_revoked_safely(): void
    {
        $user = $this->createUser('missing@example.test');

        $loginResponse = $this->post('/login', $this->credentials($user))->assertRedirect('/cabinet');
        $sessionId = $this->sessionIdFrom($loginResponse);
        $authSessionKey = $this->authSessionKey();
        $user->forceDelete();

        $response = $this->get('/cabinet')
            ->assertRedirect('/login')
            ->assertSessionHas('access_message', 'Доступ к учётной записи недоступен.');

        $response->assertSessionMissing($authSessionKey);
        $this->assertGuest();
        self::assertNotSame($sessionId, $this->sessionIdFrom($response));
    }

    public function test_normal_guest_request_keeps_standard_auth_redirect(): void
    {
        $this->get('/cabinet')
            ->assertRedirect('/login')
            ->assertSessionMissing('access_message');
    }

    public function test_stale_session_check_is_resolved_before_standard_auth_middleware(): void
    {
        $kernel = app(HttpKernelContract::class);
        self::assertInstanceOf(HttpKernel::class, $kernel);

        $priority = $kernel->getMiddlewarePriority();
        $staleSessionPosition = array_search(RevokeStaleAuthenticatedSession::class, $priority, true);
        $authPosition = array_search(AuthenticatesRequests::class, $priority, true);

        self::assertIsInt($staleSessionPosition);
        self::assertIsInt($authPosition);
        self::assertLessThan($authPosition, $staleSessionPosition);
    }

    private function authSessionKey(): string
    {
        $guard = Auth::guard('web');
        self::assertInstanceOf(SessionGuard::class, $guard);

        return $guard->getName();
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

    private function sessionIdFrom(TestResponse $response): string
    {
        $cookie = $response->getCookie((string) config('session.cookie'));

        self::assertNotNull($cookie);

        return (string) $cookie->getValue();
    }

    /**
     * @return array{email: string, password: string}
     */
    private function credentials(User $user): array
    {
        return [
            'email' => $user->email,
            'password' => 'secret-password',
        ];
    }
}
