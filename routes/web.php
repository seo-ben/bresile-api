<?php

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/switch-currency/{currency}', function (string $currency) {
    CurrencyService::setActiveCurrency($currency);
    return back();
})->middleware(['web'])->name('admin.switch-currency');

