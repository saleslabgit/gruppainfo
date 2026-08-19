<?php

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
