<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter\Tests\Unit;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use TuVendor\ErrorReporter\ErrorReporter;
use TuVendor\ErrorReporter\Mail\ErrorReportMail;
use TuVendor\ErrorReporter\Tests\TestCase;

/**
 * Class ErrorReporterTest
 *
 * @package TuVendor\ErrorReporter\Tests\Unit
 */
class ErrorReporterTest extends TestCase
{
    protected ErrorReporter $reporter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reporter = $this->app->make(ErrorReporter::class);

        Mail::fake();
    }

    /** @test */
    public function it_sends_an_email_when_an_exception_is_reported(): void
    {
        $exception = new InvalidArgumentException('Boom');

        $this->reporter->report($exception);

        Mail::assertSent(ErrorReportMail::class, function (ErrorReportMail $mail) {
            return $mail->hasTo('dev@example.com')
                && $mail->context['exception']['message'] === 'Boom';
        });
    }

    /** @test */
    public function it_does_not_send_when_disabled(): void
    {
        config(['error-reporter.enabled' => false]);

        $this->reporter->report(new InvalidArgumentException('Boom'));

        Mail::assertNothingSent();
    }

    /** @test */
    public function it_does_not_send_in_a_non_allowed_environment(): void
    {
        config(['error-reporter.environments' => ['production']]);

        $this->reporter->report(new InvalidArgumentException('Boom'));

        Mail::assertNothingSent();
    }

    /** @test */
    public function it_does_not_send_when_no_recipients_are_configured(): void
    {
        config(['error-reporter.recipients' => []]);

        $this->reporter->report(new InvalidArgumentException('Boom'));

        Mail::assertNothingSent();
    }

    /** @test */
    public function it_skips_ignored_exceptions(): void
    {
        config([
            'error-reporter.ignored_exceptions' => [
                AuthenticationException::class,
            ],
        ]);

        $this->reporter->report(new AuthenticationException('Nope'));

        Mail::assertNothingSent();
    }

    /** @test */
    public function it_throttles_identical_errors(): void
    {
        $exception = new InvalidArgumentException('Boom');

        $this->reporter->report($exception);
        $this->reporter->report($exception);
        $this->reporter->report($exception);

        Mail::assertSent(ErrorReportMail::class, 1);
    }

    /** @test */
    public function it_does_not_throttle_different_errors(): void
    {
        $this->reporter->report(new InvalidArgumentException('First'));
        $this->reporter->report(new InvalidArgumentException('Second'));

        Mail::assertSent(ErrorReportMail::class, 2);
    }

    /** @test */
    public function it_can_disable_throttling(): void
    {
        config(['error-reporter.throttle_seconds' => 0]);

        $exception = new InvalidArgumentException('Boom');

        $this->reporter->report($exception);
        $this->reporter->report($exception);

        Mail::assertSent(ErrorReportMail::class, 2);
    }

    /** @test */
    public function it_sends_to_multiple_recipients(): void
    {
        config(['error-reporter.recipients' => [
            'dev@example.com',
            'ops@example.com',
        ]]);

        $this->reporter->report(new InvalidArgumentException('Boom'));

        Mail::assertSent(ErrorReportMail::class, function (ErrorReportMail $mail) {
            return $mail->hasTo('dev@example.com')
                && $mail->hasTo('ops@example.com');
        });
    }

    /** @test */
    public function it_never_throws_when_mail_sending_fails(): void
    {
        Mail::shouldReceive('mailer')
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        // No exception should bubble up.
        $this->reporter->report(new InvalidArgumentException('Boom'));

        $this->assertTrue(true);
    }
}