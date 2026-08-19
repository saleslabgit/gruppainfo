<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\User\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class AuthenticationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_render_login_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Вход в систему')
            ->assertSee('data-ui-password', false);
    }

    public function test_approved_enabled_psychologist_authenticates_and_reaches_cabinet(): void
    {
        $user = $this->createUser();

        $this->get('/login');
        $sessionId = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect('/cabinet');

        $this->assertAuthenticatedAs($user);
        self::assertNotSame($sessionId, session()->getId());
        $this->get('/cabinet')->assertOk()->assertSee($user->email);
    }

    public function test_approved_enabled_administrator_authenticates_and_reaches_admin(): void
    {
        $admin = $this->createUser(['email' => 'admin@example.test', 'admin' => true]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertOk()->assertSee('Роль: администратор');
    }

    public function test_successful_login_ignores_an_intended_route_outside_the_users_role(): void
    {
        $psychologist = $this->createUser();

        $this->get('/admin')->assertRedirect('/login');
        $this->post('/login', [
            'email' => $psychologist->email,
            'password' => 'secret-password',
        ])->assertRedirect('/cabinet');
    }

    public function test_wrong_password_fails_with_generic_error(): void
    {
        $user = $this->createUser();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Не удалось войти с указанными данными.']);

        $this->assertGuest();
    }

    public function test_unknown_email_uses_the_same_generic_error(): void
    {
        $this->from('/login')->post('/login', [
            'email' => 'unknown@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Не удалось войти с указанными данными.']);

        $this->assertGuest();
    }

    #[DataProvider('ineligibleLoginStates')]
    public function test_ineligible_user_cannot_authenticate(UserStatus $status, bool $disabled): void
    {
        $user = $this->createUser(['status' => $status, 'disabled' => $disabled]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * @return array<string, array{UserStatus, bool}>
     */
    public static function ineligibleLoginStates(): array
    {
        return [
            'pending' => [UserStatus::Pending, false],
            'rejected' => [UserStatus::Rejected, false],
            'disabled approved' => [UserStatus::Approved, true],
        ];
    }

    public function test_soft_deleted_user_cannot_authenticate(): void
    {
        $user = $this->createUser();
        $user->delete();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_role_and_guest_boundaries_are_enforced(): void
    {
        $psychologist = $this->createUser();
        $admin = $this->createUser(['email' => 'admin@example.test', 'admin' => true]);

        $this->get('/admin')->assertRedirect('/login');
        $this->get('/cabinet')->assertRedirect('/login');

        $this->actingAs($psychologist)->get('/admin')->assertForbidden();
        $this->actingAs($admin)->get('/cabinet')->assertForbidden();
    }

    public function test_authenticated_user_is_redirected_away_from_guest_login_flow(): void
    {
        $psychologist = $this->createUser();
        $admin = $this->createUser(['email' => 'admin@example.test', 'admin' => true]);

        $this->actingAs($psychologist)->get('/login')->assertRedirect('/cabinet');
        $this->actingAs($admin)->get('/login')->assertRedirect('/admin');
    }

    public function test_logout_invalidates_session_and_protected_access(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->withSession(['marker' => 'present']);
        $sessionId = session()->getId();
        $token = session()->token();

        $this->post('/logout')
            ->assertRedirect('/login')
            ->assertSessionHas('status', 'Вы вышли из системы.');

        $this->assertGuest();
        self::assertNotSame($sessionId, session()->getId());
        self::assertNotSame($token, session()->token());
        $this->assertFalse(session()->has('marker'));
        $this->get('/cabinet')->assertRedirect('/login');
    }

    #[DataProvider('revokedStates')]
    public function test_access_is_revoked_on_next_request(array $changes): void
    {
        $user = $this->createUser();
        $this->actingAs($user)->get('/cabinet')->assertOk();

        User::query()->whereKey($user->getKey())->update($changes);

        $this->get('/cabinet')
            ->assertRedirect('/login')
            ->assertSessionHas('access_message');
        $this->assertGuest();
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function revokedStates(): array
    {
        return [
            'disabled' => [['disabled' => true]],
            'pending' => [['status' => UserStatus::Pending->value]],
            'rejected' => [['status' => UserStatus::Rejected->value]],
            'soft deleted' => [['deleted_at' => '2026-08-19 12:00:00']],
        ];
    }

    public function test_login_is_rate_limited_at_the_configured_threshold(): void
    {
        config()->set('auth.login.max_attempts', 2);
        $user = $this->createUser();
        $credentials = ['email' => $user->email, 'password' => 'wrong-password'];

        $this->post('/login', $credentials)->assertSessionHasErrors('email');
        $this->post('/login', $credentials)->assertSessionHasErrors('email');
        $response = $this->post('/login', $credentials);

        $response->assertSessionHasErrors('email');
        self::assertStringStartsWith(
            'Слишком много попыток.',
            (string) session('errors')->first('email'),
        );

        $this->assertGuest();
    }

    public function test_successful_login_clears_previous_failed_attempts(): void
    {
        $user = $this->createUser();
        $key = strtolower($user->email).'|127.0.0.1';

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
        self::assertSame(1, RateLimiter::attempts($key));

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-password'])
            ->assertRedirect('/cabinet');

        self::assertSame(0, RateLimiter::attempts($key));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'email' => 'psychologist@example.test',
            'password' => 'secret-password',
            'status' => UserStatus::Approved,
            'disabled' => false,
            'admin' => false,
            'free' => false,
        ], $attributes));
    }
}
