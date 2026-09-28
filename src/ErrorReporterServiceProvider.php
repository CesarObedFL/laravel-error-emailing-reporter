<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Throwable;
use TuVendor\ErrorReporter\Support\ErrorContextCollector;

/**
 * Class ErrorReporterServiceProvider
 *
 * Registers the package services and hooks into Laravel's exception handler.
 *
 * @package TuVendor\ErrorReporter
 */
class ErrorReporterServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/error-reporter.php',
            'error-reporter'
        );

        $this->app->singleton(ErrorContextCollector::class);

        $this->app->singleton(ErrorReporter::class, function ($app) {
            return new ErrorReporter(
                $app->make(ErrorContextCollector::class)
            );
        });
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/error-reporter.php' => config_path('error-reporter.php'),
        ], 'error-reporter-config');

        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            'error-reporter'
        );

        $this->registerExceptionHandlerHook();
    }

    /**
     * Hook into Laravel's exception reporting pipeline.
     *
     * @return void
     */
    protected function registerExceptionHandlerHook(): void
    {
        $reporter = $this->app->make(ErrorReporter::class);

        $handler = $this->app->make(ExceptionHandler::class);

        if (method_exists($handler, 'reportable')) {
            $handler->reportable(function (Throwable $exception) use ($reporter) {
                $reporter->report($exception);
            });
        }
    }
}