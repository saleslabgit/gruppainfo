<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

final class RevokeStaleAuthenticatedSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');

        if (! $guard instanceof SessionGuard) {
            throw new LogicException('Stale session revocation requires the web session guard.');
        }

        $identifier = $request->session()->get($guard->getName());

        if ($identifier === null) {
            return $next($request);
        }

        $user = is_int($identifier) || is_string($identifier)
            ? User::withTrashed()->find($identifier)
            : null;

        if ($user !== null && ! $user->trashed()) {
            return $next($request);
        }

        $guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('access_message', 'Доступ к учётной записи недоступен.');
    }
}
