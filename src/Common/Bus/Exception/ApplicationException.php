<?php
declare(strict_types=1);

namespace Gaming\Common\Bus\Exception;

use Gaming\Common\Bus\Violation;

/**
 * An application exception can happen due user input violations such as
 *  * a parameter is empty.
 *  * a parameter doesn't match to specified criteria.
 *  * a parameter tried to enforce an invariant which is not allowed.
 * An application exception can't happen in the case of
 *  * an unavailable external system (database, SMTP, HTTP).
 *  * real exceptional cases that should not happen.
 */
final class ApplicationException extends BusException
{
    /**
     * @var Violation[]
     */
    private $violations;

    /**
     * ValidationException constructor.
     *
     * @param Violation[] $violations
     */
    public function __construct(array $violations)
    {
        parent::__construct('Violations occurred');

        $this->violations = $violations;
    }

    /**
     * @return Violation[]
     */
    public function violations(): array
    {
        return $this->violations;
    }
}
