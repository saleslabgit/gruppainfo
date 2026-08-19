<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsEligible;
use App\Http\Middleware\RevokeStaleAuthenticatedSession;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'eligible' => EnsureUserIsEligible::class,
            'role' => EnsureUserHasRole::class,
            'stale-session' => RevokeStaleAuthenticatedSession::class,
        ]);

        $middleware->prependToPriorityList(
            AuthenticatesRequests::class,
            RevokeStaleAuthenticatedSession::class,
        );

        $middleware->redirectUsersTo(
            static fn (Request $request): string => $request->user()?->admin
                ? route('admin.index')
                : route('cabinet.index'),
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
