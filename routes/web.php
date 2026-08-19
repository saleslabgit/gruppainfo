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
