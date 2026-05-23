<?php

namespace Sa\RateLimitDashboard;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Sa\RateLimitDashboard\Console\CheckRateLimitAlertsCommand;
use Sa\RateLimitDashboard\Console\PruneRateLimitEventsCommand;
use Sa\RateLimitDashboard\Events\RateLimitHit;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;
use Sa\RateLimitDashboard\Listeners\RecordRateLimitEvent;

class RateLimitDashboardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerPublishing();
        $this->registerEvents();
        $this->registerCommands();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/rate-limit-dashboard.php',
            'rate-limit-dashboard'
        );
    }

    /**
     * Register the package configuration.
     *
     * @return void
     */
    protected function registerConfig()
    {
        // No additional action needed if we just merge and publish config.
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if ($this->app['config']->get('rate-limit-dashboard.enabled')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }

    /**
     * Register the package views.
     *
     * @return void
     */
    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rate-limit-dashboard');
    }

    /**
     * Register the package publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/rate-limit-dashboard.php' => config_path('rate-limit-dashboard.php'),
            ], 'rate-limit-dashboard-config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'rate-limit-dashboard-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/rate-limit-dashboard'),
            ], 'rate-limit-dashboard-views');
        }
    }

    /**
     * Register the package events.
     *
     * @return void
     */
    protected function registerEvents()
    {
        Event::listen(
            RateLimitHit::class,
            RecordRateLimitEvent::class
        );
        Event::listen(
            RateLimitThrottled::class,
            RecordRateLimitEvent::class
        );
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckRateLimitAlertsCommand::class,
                PruneRateLimitEventsCommand::class,
            ]);
        }
    }
}
