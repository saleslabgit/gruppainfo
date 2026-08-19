<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\User\UserStatus;
use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsEligible
{
    public function handle(Request $request, Closure $next): Response
    {
        $authenticatedUser = $request->user();
        $user = $authenticatedUser === null
            ? null
            : User::withTrashed()->find($authenticatedUser->getAuthIdentifier());

        if ($user === null || $user->trashed() || $user->status !== UserStatus::Approved || $user->disabled) {
            return $this->revokeAccess($request);
        }

        Auth::guard('web')->setUser($user);
        $request->setUserResolver(static fn (): User => $user);

        return $next($request);
    }

    private function revokeAccess(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('access_message', 'Доступ к учётной записи недоступен.');
    }
}
