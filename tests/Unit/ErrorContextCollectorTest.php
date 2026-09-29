<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter\Tests\Unit;

use Illuminate\Http\Request;
use InvalidArgumentException;
use TuVendor\ErrorReporter\Support\ErrorContextCollector;
use TuVendor\ErrorReporter\Tests\TestCase;

/**
 * Class ErrorContextCollectorTest
 *
 * @package TuVendor\ErrorReporter\Tests\Unit
 */
class ErrorContextCollectorTest extends TestCase
{
    protected ErrorContextCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collector = $this->app->make(ErrorContextCollector::class);
    }

    /** @test */
    public function it_collects_project_information(): void
    {
        $context = $this->collector->collect(new InvalidArgumentException('Boom'));

        $this->assertSame('TestApp', $context['project']['name']);
        $this->assertSame('1.2.3', $context['project']['version']);
        $this->assertSame('https://test.local', $context['project']['url']);
        $this->assertSame('testing', $context['environment']);
    }

    /** @test */
    public function it_collects_exception_details(): void
    {
        $exception = new InvalidArgumentException('Something failed', 42);

        $context = $this->collector->collect($exception);

        $this->assertSame(InvalidArgumentException::class, $context['exception']['class']);
        $this->assertSame('Something failed', $context['exception']['message']);
        $this->assertSame(42, $context['exception']['code']);
        $this->assertSame($exception->getFile(), $context['exception']['file']);
        $this->assertSame($exception->getLine(), $context['exception']['line']);
        $this->assertNotEmpty($context['exception']['trace']);
    }

    /** @test */
    public function it_includes_previous_exception_when_present(): void
    {
        $previous  = new \RuntimeException('Root cause');
        $exception = new InvalidArgumentException('Wrapper', 0, $previous);

        $context = $this->collector->collect($exception);

        $this->assertNotNull($context['exception']['previous']);
        $this->assertStringContainsString('RuntimeException', $context['exception']['previous']);
        $this->assertStringContainsString('Root cause', $context['exception']['previous']);
    }

    /** @test */
    public function it_collects_request_information(): void
    {
        $request = Request::create('/dashboard?foo=bar', 'POST', [
            'username' => 'john',
            'password' => 'secret123',
        ]);
        $this->app->instance('request', $request);

        $context = $this->collector->collect(new InvalidArgumentException('Boom'));

        $this->assertSame('POST', $context['request']['method']);
        $this->assertStringContainsString('/dashboard', $context['request']['url']);
        $this->assertSame('john', $context['request']['input']['username']);
    }

    /** @test */
    public function it_redacts_sensitive_input_fields(): void
    {
        $request = Request::create('/login', 'POST', [
            'email'    => 'user@example.com',
            'password' => 'super-secret',
            'token'    => 'abc123',
            'secret'   => 'my-secret',
        ]);
        $this->app->instance('request', $request);

        $context = $this->collector->collect(new InvalidArgumentException('Boom'));

        $this->assertSame('***REDACTED***', $context['request']['input']['password']);
        $this->assertSame('***REDACTED***', $context['request']['input']['token']);
        $this->assertSame('***REDACTED***', $context['request']['input']['secret']);
        $this->assertSame('user@example.com', $context['request']['input']['email']);
    }

    /** @test */
    public function it_redacts_sensitive_headers(): void
    {
        $request = Request::create('/api/endpoint', 'GET');
        $request->headers->set('Authorization', 'Bearer secret-token');
        $request->headers->set('X-Api-Key', 'my-api-key');
        $request->headers->set('Accept', 'application/json');
        $this->app->instance('request', $request);

        $context = $this->collector->collect(new InvalidArgumentException('Boom'));

        $this->assertSame(['***REDACTED***'], $context['request']['headers']['authorization']);
        $this->assertSame(['***REDACTED***'], $context['request']['headers']['x-api-key']);
        $this->assertSame(['application/json'], $context['request']['headers']['accept']);
    }

    /** @test */
    public function it_excludes_request_when_disabled_by_config(): void
    {
        config(['error-reporter.include_request' => false]);

        $context = $this->collector->collect(new InvalidArgumentException('Boom'));

        $this->assertSame([], $context['request']);
    }
}