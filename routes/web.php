<?php

use Illuminate\Support\Facades\Route;
use Sa\RateLimitDashboard\Http\Controllers\ApiController;
use Sa\RateLimitDashboard\Http\Controllers\DashboardController;

Route::prefix(config('rate-limit-dashboard.dashboard.prefix', 'admin/rate-limits'))
    ->middleware(config('rate-limit-dashboard.dashboard.middleware', ['web']))
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('rate-limit-dashboard.index');
        Route::post('/configs', [DashboardController::class, 'storeConfig'])->name('rate-limit-dashboard.configs.store');
        Route::delete('/configs/{limiterName}', [DashboardController::class, 'destroyConfig'])->name('rate-limit-dashboard.configs.destroy');

        Route::prefix('api')->group(function (): void {
            Route::get('/metrics', [ApiController::class, 'metrics'])->name('rate-limit-dashboard.api.metrics');
            Route::get('/offenders', [ApiController::class, 'offenders'])->name('rate-limit-dashboard.api.offenders');
            Route::get('/configs', [ApiController::class, 'configs'])->name('rate-limit-dashboard.api.configs');
            Route::get('/summaries', [ApiController::class, 'summaries'])->name('rate-limit-dashboard.api.summaries');
            Route::get('/checks', [ApiController::class, 'checks'])->name('rate-limit-dashboard.api.checks');
        });
    });
