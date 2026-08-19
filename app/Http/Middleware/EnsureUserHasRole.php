<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $isAllowed = match ($role) {
            'admin' => $request->user()?->admin === true,
            'psychologist' => $request->user()?->admin === false,
            default => false,
        };

        abort_unless($isAllowed, 403);

        return $next($request);
    }
}
