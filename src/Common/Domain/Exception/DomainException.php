<?php

declare(strict_types=1);

namespace Gaming\Common\Domain\Exception;

use Exception;

/**
 * Represents an exception raised when a domain invariant is violated.
 * It's meant to be used as a Layer Supertype that encapsulates violations
 * that can be translated into user-friendly error messages.
 */
class DomainException extends Exception
{
    public function __construct(
        public readonly Violations $violations
    ) {
        parent::__construct('Domain exception occurred with ' . $violations->count() . ' violation(s).');
    }
}
