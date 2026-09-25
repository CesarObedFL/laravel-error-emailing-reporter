<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Class ErrorReportMail
 *
 * Mailable that wraps the error report data.
 *
 * @package TuVendor\ErrorReporter\Mail
 */
class ErrorReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public array $context,
    ) {
    }

    /**
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        $prefix  = config('error-reporter.subject_prefix', '[ERROR]');
        $project = $this->context['project']['name'] ?? 'App';
        $class   = $this->context['exception']['class'] ?? 'Exception';

        return new Envelope(
            subject: sprintf('%s %s - %s', $prefix, $project, class_basename($class)),
        );
    }

    /**
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'error-reporter::emails.error-report',
            with: ['context' => $this->context],
        );
    }
}