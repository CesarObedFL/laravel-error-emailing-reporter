<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter\Tests\Feature;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use TuVendor\ErrorReporter\Mail\ErrorReportMail;
use TuVendor\ErrorReporter\Tests\TestCase;

/**
 * Class ExceptionHandlerIntegrationTest
 *
 * @package TuVendor\ErrorReporter\Tests\Feature
 */
class ExceptionHandlerIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    /** @test */
    public function it_reports_exceptions_through_the_laravel_handler(): void
    {
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report(new InvalidArgumentException('Reported via handler'));

        Mail::assertSent(ErrorReportMail::class, function (ErrorReportMail $mail) {
            return $mail->context['exception']['message'] === 'Reported via handler';
        });
    }

    /** @test */
    public function it_respects_the_enabled_flag_through_the_handler(): void
    {
        config(['error-reporter.enabled' => false]);

        $handler = $this->app->make(ExceptionHandler::class);
        $handler->report(new InvalidArgumentException('Should not be reported'));

        Mail::assertNothingSent();
    }
}