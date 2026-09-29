<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter\Tests\Feature;

use TuVendor\ErrorReporter\ErrorReporter;
use TuVendor\ErrorReporter\Support\ErrorContextCollector;
use TuVendor\ErrorReporter\Tests\TestCase;

/**
 * Class ServiceProviderTest
 *
 * @package TuVendor\ErrorReporter\Tests\Feature
 */
class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_the_configuration(): void
    {
        $this->assertNotNull(config('error-reporter'));
        $this->assertTrue(config('error-reporter.enabled'));
        $this->assertSame(['dev@example.com'], config('error-reporter.recipients'));
    }

    /** @test */
    public function it_registers_the_error_reporter_as_singleton(): void
    {
        $first  = $this->app->make(ErrorReporter::class);
        $second = $this->app->make(ErrorReporter::class);

        $this->assertSame($first, $second);
    }

    /** @test */
    public function it_registers_the_context_collector_as_singleton(): void
    {
        $first  = $this->app->make(ErrorContextCollector::class);
        $second = $this->app->make(ErrorContextCollector::class);

        $this->assertSame($first, $second);
    }

    /** @test */
    public function it_registers_the_view_namespace(): void
    {
        $this->assertTrue(
            view()->exists('error-reporter::emails.error-report')
        );
    }

    /** @test */
    public function the_config_is_publishable(): void
    {
        $paths = ServiceProvider::pathsToPublish(
            \TuVendor\ErrorReporter\ErrorReporterServiceProvider::class,
            'error-reporter-config'
        );

        $this->assertNotEmpty($paths);

        $targets = array_values($paths);

        $this->assertStringContainsString('error-reporter.php', $targets[0]);
    }
}