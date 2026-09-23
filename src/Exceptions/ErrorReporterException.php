<?php

declare(strict_types=1);

namespace TuVendor\ErrorReporter\Exceptions;

use RuntimeException;

/**
 * Class ErrorReporterException
 *
 * Thrown when the error reporter itself fails.
 *
 * @package TuVendor\ErrorReporter\Exceptions
 */
class ErrorReporterException extends RuntimeException
{
}