<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Support\DateTimeFormatter;
use App\Support\MoneyFormatter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'displayedAt' => DateTimeFormatter::format(now('UTC')),
        'samplePrice' => MoneyFormatter::format(12500),
    ]);
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['stale-session', 'auth', 'eligible'])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::view('/', 'admin.index')->name('index');
    });

    Route::middleware('role:psychologist')->prefix('cabinet')->name('cabinet.')->group(function (): void {
        Route::view('/', 'cabinet.index')->name('index');
    });
});

Route::get('/ui-kit', function () {
    abort_unless(app()->environment(['local', 'testing']), 404);

    $paginationPath = route('ui-kit');

    return view('ui-kit', [
        'firstPagePaginator' => new LengthAwarePaginator(
            range(1, 20),
            124,
            20,
            1,
            ['path' => $paginationPath],
        ),
        'middlePagePaginator' => new LengthAwarePaginator(
            range(61, 80),
            124,
            20,
            4,
            ['path' => $paginationPath],
        ),
    ]);
})->name('ui-kit');
