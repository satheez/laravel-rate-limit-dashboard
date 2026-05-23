<?php

use Illuminate\Support\Facades\Route;
use Sa\RateLimitDashboard\Http\Controllers\DashboardController;

Route::prefix(config('rate-limit-dashboard.dashboard.prefix', 'admin/rate-limits'))
    ->middleware(config('rate-limit-dashboard.dashboard.middleware', ['web']))
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('rate-limit-dashboard.index');
    });
