<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter\Support;

use Illuminate\Http\Request;
use Throwable;

/**
 * Class ErrorContextCollector
 *
 * Collects contextual information about the current request and application.
 *
 * @package TuVendor\ErrorReporter\Support
 */
class ErrorContextCollector
{
    /**
     * Collect all relevant context data for an error.
     *
     * @param  \Throwable  $exception
     * @return array<string, mixed>
     */
    public function collect(Throwable $exception): array
    {
        return [
            'project'    => $this->getProjectInfo(),
            'environment'=> app()->environment(),
            'timestamp'  => now()->toIso8601String(),
            'php'        => PHP_VERSION,
            'laravel'    => app()->version(),
            'exception'  => $this->getExceptionInfo($exception),
            'request'    => $this->getRequestInfo(),
            'user'       => $this->getUserInfo(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getProjectInfo(): array
    {
        return [
            'name'    => config('app.name', 'Laravel'),
            'version' => config('app.version', '1.0.0'),
            'url'     => config('app.url'),
        ];
    }

    /**
     * @param  \Throwable  $exception
     * @return array<string, mixed>
     */
    protected function getExceptionInfo(Throwable $exception): array
    {
        return [
            'class'      => get_class($exception),
            'message'    => $exception->getMessage(),
            'code'       => $exception->getCode(),
            'file'       => $exception->getFile(),
            'line'       => $exception->getLine(),
            'trace'      => $exception->getTraceAsString(),
            'previous'   => $exception->getPrevious()
                ? get_class($exception->getPrevious()).': '.$exception->getPrevious()->getMessage()
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRequestInfo(): array
    {
        if (! config('error-reporter.include_request', true)) {
            return [];
        }

        /** @var Request|null $request */
        $request = request();

        if (! $request) {
            return [];
        }

        return [
            'url'     => $request->fullUrl(),
            'method'  => $request->method(),
            'ip'      => $request->ip(),
            'headers' => config('error-reporter.include_headers', true)
                ? $this->sanitizeHeaders($request->headers->all())
                : [],
            'input'   => $this->sanitizeInput($request->except(['password', 'password_confirmation', 'token'])),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getUserInfo(): array
    {
        if (! auth()->check()) {
            return [];
        }

        $user = auth()->user();

        return [
            'id'    => $user->getAuthIdentifier(),
            'email' => $user->email ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $headers
     * @return array<string, mixed>
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitive = ['authorization', 'cookie', 'x-api-key'];

        foreach ($sensitive as $key) {
            if (isset($headers[$key])) {
                $headers[$key] = ['***REDACTED***'];
            }
        }

        return $headers;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function sanitizeInput(array $input): array
    {
        $sensitive = ['password', 'password_confirmation', 'token', 'secret'];

        foreach ($sensitive as $key) {
            if (array_key_exists($key, $input)) {
                $input[$key] = '***REDACTED***';
            }
        }

        return $input;
    }
}