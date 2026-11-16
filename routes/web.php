<?php

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ops/setup-db', function () {
    config(['session.driver' => 'array', 'cache.default' => 'array']);

    Artisan::call('migrate', ['--force' => true]);
    $migrateOutput = Artisan::output();

    Artisan::call('db:seed', ['--force' => true]);
    $seedOutput = Artisan::output();

    return response()->json([
        'status' => 'success',
        'migrate' => $migrateOutput,
        'seed' => $seedOutput,
    ]);
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
]);

Route::get('/admin/switch-currency/{currency}', function (string $currency) {
    CurrencyService::setActiveCurrency($currency);
    return back();
})->middleware(['web'])->name('admin.switch-currency');

