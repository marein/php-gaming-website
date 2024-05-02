<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Request;
use Gaming\Common\Bus\Violation;
use Gaming\Common\Bus\ViolationParameter;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SymfonyValidatorBus implements Bus
{
    public function __construct(
        private readonly Bus $bus,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function handle(Request $request): mixed
    {
        $symfonyViolations = $this->validator->validate($request);

        if ($symfonyViolations->count() > 0) {
            throw new ApplicationException(
                $this->mapFromSymfonyViolations($symfonyViolations)
            );
        }

        return $this->bus->handle($request);
    }

    /**
     * @param ConstraintViolationListInterface<ConstraintViolationInterface> $symfonyViolations
     *
     * @return Violation[]
     */
    private function mapFromSymfonyViolations(ConstraintViolationListInterface $symfonyViolations): array
    {
        return array_map(
            fn(ConstraintViolationInterface $constraintViolation): Violation => new Violation(
                $constraintViolation->getPropertyPath(),
                $constraintViolation->getMessageTemplate(),
                $this->mapFromSymfonyParameters($constraintViolation->getParameters())
            ),
            iterator_to_array($symfonyViolations)
        );
    }

    /**
     * Removes template characters from parameter names.
     *
     * @param array<string, bool|int|float|string> $symfonyParameters
     *
     * @return ViolationParameter[]
     */
    private function mapFromSymfonyParameters(array $symfonyParameters): array
    {
        return array_map(
            fn(string $name, bool|int|float|string $value): ViolationParameter => new ViolationParameter(
                str_replace(['{{ ', ' }}'], '', $name),
                $value
            ),
            array_keys($symfonyParameters),
            $symfonyParameters
        );
    }
}
