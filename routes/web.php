<?php

use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

$opsExcludedMiddleware = [
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
];

Route::get('/ops', function () {
    config(['session.driver' => 'array', 'cache.default' => 'array']);
    return view('ops.console');
})->withoutMiddleware($opsExcludedMiddleware);

Route::post('/ops', function (Request $request) {
    config(['session.driver' => 'array', 'cache.default' => 'array']);

    $preset = $request->input('preset');
    $custom = trim((string) $request->input('custom_command', ''));
    $output = '';
    $status = 'success';
    $executedCommand = '';

    try {
        if ($preset === 'migrate_and_seed') {
            $executedCommand = 'migrate --force && db:seed --force';
            Artisan::call('migrate', ['--force' => true]);
            $output .= "=== MIGRATE ===\n" . Artisan::output() . "\n";
            Artisan::call('db:seed', ['--force' => true]);
            $output .= "=== DB:SEED ===\n" . Artisan::output();
        } elseif ($preset === 'migrate') {
            $executedCommand = 'migrate --force';
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
        } elseif ($preset === 'seed_all') {
            $executedCommand = 'db:seed --force';
            Artisan::call('db:seed', ['--force' => true]);
            $output = Artisan::output();
        } elseif ($preset === 'seed_admin') {
            $executedCommand = 'db:seed --class=AdminUserSeeder --force';
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AdminUserSeeder', '--force' => true]);
            $output = Artisan::output() ?: "AdminUserSeeder exécuté avec succès (admin@ecole-bresil.com).";
        } elseif ($preset === 'seed_academic') {
            $executedCommand = 'db:seed AcademicReferenceDataSeeder + PartnerUniversitySeeder';
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AcademicReferenceDataSeeder', '--force' => true]);
            $output .= "=== AcademicReferenceDataSeeder ===\n" . (Artisan::output() ?: "OK\n");
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PartnerUniversitySeeder', '--force' => true]);
            $output .= "=== PartnerUniversitySeeder ===\n" . (Artisan::output() ?: "OK\n");
        } elseif ($preset === 'migrate_status') {
            $executedCommand = 'migrate:status';
            Artisan::call('migrate:status');
            $output = Artisan::output();
        } elseif ($preset === 'optimize_clear') {
            $executedCommand = 'optimize:clear';
            Artisan::call('optimize:clear');
            $output = Artisan::output();
        } elseif ($preset === 'filament_assets') {
            $executedCommand = 'filament:assets';
            Artisan::call('filament:assets');
            $output = Artisan::output();
        } elseif ($preset === 'migrate_fresh_seed') {
            $executedCommand = 'migrate:fresh --seed --force';
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            $output = Artisan::output();
        } elseif ($custom !== '') {
            $custom = preg_replace('/^php\s+artisan\s+/i', '', $custom);
            $executedCommand = $custom;
            Artisan::call($custom);
            $output = Artisan::output();
        }
    } catch (\Throwable $e) {
        $status = 'error';
        $output .= "\n[ERREUR] " . $e->getMessage() . "\n\n" . $e->getTraceAsString();
    }

    if (trim($output) === '') {
        $output = "Commande exécutée avec succès (aucune sortie texte retournée).";
    }

    return view('ops.console', [
        'output' => $output,
        'status' => $status,
        'executedCommand' => $executedCommand,
    ]);
})->withoutMiddleware($opsExcludedMiddleware);

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
})->withoutMiddleware($opsExcludedMiddleware);

Route::get('/admin/switch-currency/{currency}', function (string $currency) {
    CurrencyService::setActiveCurrency($currency);
    return back();
})->middleware(['web'])->name('admin.switch-currency');
