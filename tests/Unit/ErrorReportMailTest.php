<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter\Tests\Unit;

use TuVendor\ErrorReporter\Mail\ErrorReportMail;
use TuVendor\ErrorReporter\Tests\TestCase;

/**
 * Class ErrorReportMailTest
 *
 * @package TuVendor\ErrorReporter\Tests\Unit
 */
class ErrorReportMailTest extends TestCase
{
    /** @test */
    public function it_builds_the_subject_with_prefix_project_and_exception_class(): void
    {
        config(['error-reporter.subject_prefix' => '[PROD]']);

        $mail = new ErrorReportMail([
            'project' => ['name' => 'MyApp'],
            'exception' => [
                'class' => \InvalidArgumentException::class,
            ],
        ]);

        $envelope = $mail->envelope();

        $this->assertSame('[PROD] MyApp - InvalidArgumentException', $envelope->subject);
    }

    /** @test */
    public function it_uses_the_configured_view(): void
    {
        $mail = new ErrorReportMail([
            'project' => ['name' => 'MyApp'],
            'exception' => [
                'class' => \InvalidArgumentException::class,
                'message' => 'Boom',
                'file' => __FILE__,
                'line' => 42,
                'trace' => 'stack trace here',
            ],
            'environment' => 'production',
            'timestamp' => '2024-01-01T00:00:00+00:00',
            'php' => PHP_VERSION,
            'laravel' => '11.0',
        ]);

        $content = $mail->content();

        $this->assertSame('error-reporter::emails.error-report', $content->view);
        $this->assertArrayHasKey('context', $content->with);
        $this->assertSame('MyApp', $content->with['context']['project']['name']);
    }

    /** @test */
    public function the_view_renders_the_main_context_fields(): void
    {
        $context = [
            'project' => [
                'name' => 'MyApp',
                'version' => '9.9.9',
                'url' => 'https://myapp.test',
            ],
            'environment' => 'production',
            'timestamp' => '2024-01-01T00:00:00+00:00',
            'php' => '8.2.0',
            'laravel' => '11.0',
            'exception' => [
                'class' => \InvalidArgumentException::class,
                'message' => 'Something broke',
                'code' => 500,
                'file' => '/app/Http/Controllers/Foo.php',
                'line' => 123,
                'trace' => '#0 ...',
                'previous' => null,
            ],
            'request' => [],
            'user' => [],
        ];

        $html = view('error-reporter::emails.error-report', [
            'context' => $context,
        ])->render();

        $this->assertStringContainsString('MyApp', $html);
        $this->assertStringContainsString('9.9.9', $html);
        $this->assertStringContainsString('Something broke', $html);
        $this->assertStringContainsString('/app/Http/Controllers/Foo.php', $html);
        $this->assertStringContainsString('123', $html);
    }
}