<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use TuVendor\ErrorReporter\Mail\ErrorReportMail;
use TuVendor\ErrorReporter\Support\ErrorContextCollector;

/**
 * Class ErrorReporter
 *
 * Responsible for deciding whether an exception should be reported
 * and for dispatching the report email.
 *
 * @package TuVendor\ErrorReporter
 */
class ErrorReporter
{
    /**
     * @param  \TuVendor\ErrorReporter\Support\ErrorContextCollector  $collector
     */
    public function __construct(
        protected ErrorContextCollector $collector,
    ) {
    }

    /**
     * Report an exception by email.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        try {
            if (! $this->shouldReport($exception)) {
                return;
            }

            if ($this->isThrottled($exception)) {
                return;
            }

            $this->send($exception);
        } catch (Throwable $inner) {
            // Never let the reporter break the application.
            Log::error('ErrorReporter failed: '.$inner->getMessage());
        }
    }

    /**
     * Determine if the given exception must be reported.
     *
     * @param  \Throwable  $exception
     * @return bool
     */
    protected function shouldReport(Throwable $exception): bool
    {
        if (! config('error-reporter.enabled', true)) {
            return false;
        }

        $environments = (array) config('error-reporter.environments', ['production']);

        if (! app()->environment($environments)) {
            return false;
        }

        $ignored = (array) config('error-reporter.ignored_exceptions', []);

        foreach ($ignored as $ignoredClass) {
            if ($exception instanceof $ignoredClass) {
                return false;
            }
        }

        if (empty(config('error-reporter.recipients', []))) {
            return false;
        }

        return true;
    }

    /**
     * Check if the same error was recently reported.
     *
     * @param  \Throwable  $exception
     * @return bool
     */
    protected function isThrottled(Throwable $exception): bool
    {
        $seconds = (int) config('error-reporter.throttle_seconds', 60);

        if ($seconds <= 0) {
            return false;
        }

        $key = 'error-reporter:'.md5(
            get_class($exception).'|'.$exception->getFile().'|'.$exception->getLine().'|'.$exception->getMessage()
        );

        if (Cache::has($key)) {
            return true;
        }

        Cache::put($key, true, $seconds);

        return false;
    }

    /**
     * Send the report email.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    protected function send(Throwable $exception): void
    {
        $context    = $this->collector->collect($exception);
        $recipients = (array) config('error-reporter.recipients', []);

        $mailer = Mail::mailer(config('error-reporter.mailer') ?: null);

        $pending = $mailer->to($recipients);

        $from = config('error-reporter.from');

        if (! empty($from)) {
            $pending->from($from);
        }

        $pending->send(new ErrorReportMail($context));
    }
}