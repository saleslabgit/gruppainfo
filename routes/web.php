<?php

use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\GroupModerationController;
use App\Http\Controllers\Admin\PsychologistActionController;
use App\Http\Controllers\Admin\PsychologistController;
use App\Http\Controllers\Admin\PsychologistDocumentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Cabinet\GroupController as CabinetGroupController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\UserDocumentController;
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
        Route::resource('psychologists', PsychologistController::class);
        Route::resource('groups', AdminGroupController::class);
        Route::post('groups/{group}/revision', [GroupModerationController::class, 'revision'])->name('groups.revision');
        Route::post('groups/{group}/reject', [GroupModerationController::class, 'reject'])->name('groups.reject');
        Route::post('groups/{group}/approve', [GroupModerationController::class, 'approve'])->name('groups.approve');
        Route::post('groups/{group}/activate', [GroupModerationController::class, 'activate'])->name('groups.activate');
        Route::post('psychologists/{psychologist}/approve', [PsychologistActionController::class, 'approve'])->name('psychologists.approve');
        Route::post('psychologists/{psychologist}/reject', [PsychologistActionController::class, 'reject'])->name('psychologists.reject');
        Route::patch('psychologists/{psychologist}/tariff', [PsychologistActionController::class, 'tariff'])->name('psychologists.tariff');
        Route::post('psychologists/{psychologist}/disable', [PsychologistActionController::class, 'disable'])->name('psychologists.disable');
        Route::post('psychologists/{psychologist}/enable', [PsychologistActionController::class, 'enable'])->name('psychologists.enable');
        Route::post('psychologists/{psychologist}/documents', [PsychologistDocumentController::class, 'store'])->name('psychologists.documents.store');
        Route::delete('psychologists/{psychologist}/documents/{document}', [PsychologistDocumentController::class, 'destroy'])->name('psychologists.documents.destroy');
    });

    Route::middleware('role:psychologist')->prefix('cabinet')->name('cabinet.')->group(function (): void {
        Route::get('/', [CabinetController::class, 'index'])->name('index');
        Route::get('/groups', [CabinetGroupController::class, 'index'])->name('groups');
        Route::post('/groups', [CabinetGroupController::class, 'store'])->name('groups.store');
        Route::get('/groups/{group}', [CabinetGroupController::class, 'show'])->name('groups.show');
        Route::get('/groups/{group}/edit', [CabinetGroupController::class, 'edit'])->name('groups.edit');
        Route::put('/groups/{group}', [CabinetGroupController::class, 'update'])->name('groups.update');
        Route::post('/groups/{group}/submit', [CabinetGroupController::class, 'submit'])->name('groups.submit');
        Route::delete('/groups/{group}', [CabinetGroupController::class, 'destroy'])->name('groups.destroy');
        Route::get('/profile', [CabinetController::class, 'profile'])->name('profile');
    });

    Route::get('/documents/{document}/view', [UserDocumentController::class, 'view'])->name('documents.view');
    Route::get('/documents/{document}/download', [UserDocumentController::class, 'download'])->name('documents.download');
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
