<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use TuVendor\ErrorReporter\ErrorReporterServiceProvider;

/**
 * Class TestCase
 *
 * Base test case for the ErrorReporter package.
 *
 * @package TuVendor\ErrorReporter\Tests
 */
abstract class TestCase extends Orchestra
{
    /**
     * Register package service providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ErrorReporterServiceProvider::class,
        ];
    }

    /**
     * Define the environment for the tests.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.name', 'TestApp');
        $app['config']->set('app.version', '1.2.3');
        $app['config']->set('app.url', 'https://test.local');

        $app['config']->set('mail.default', 'array');

        $app['config']->set('error-reporter.enabled', true);
        $app['config']->set('error-reporter.environments', ['testing']);
        $app['config']->set('error-reporter.recipients', ['dev@example.com']);
        $app['config']->set('error-reporter.throttle_seconds', 60);
        $app['config']->set('error-reporter.ignored_exceptions', []);
        $app['config']->set('error-reporter.subject_prefix', '[ERROR]');
        $app['config']->set('error-reporter.mailer', null);
        $app['config']->set('error-reporter.from', null);
        $app['config']->set('error-reporter.include_request', true);
        $app['config']->set('error-reporter.include_headers', true);
    }
}