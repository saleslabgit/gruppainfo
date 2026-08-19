<?php

use App\Support\DateTimeFormatter;
use App\Support\MoneyFormatter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'displayedAt' => DateTimeFormatter::format(now('UTC')),
        'samplePrice' => MoneyFormatter::format(12500),
    ]);
});

Route::get('/ui-kit', function () {
    abort_unless(app()->environment(['local', 'testing']), 404);

    return view('ui-kit');
})->name('ui-kit');
