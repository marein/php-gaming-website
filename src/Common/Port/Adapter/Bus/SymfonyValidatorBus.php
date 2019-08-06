<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Bus;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Violation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SymfonyValidatorBus implements Bus
{
    /**
     * @var Bus
     */
    private $bus;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * SymfonyValidatorBus constructor.
     *
     * @param Bus                $bus
     * @param ValidatorInterface $validator
     */
    public function __construct(Bus $bus, ValidatorInterface $validator)
    {
        $this->bus = $bus;
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function handle(object $message)
    {
        $symfonyViolations = $this->validator->validate($message);

        if ($symfonyViolations->count() > 0) {
            throw new ApplicationException(
                $this->mapFromSymfonyViolations($symfonyViolations)
            );
        }

        return $this->bus->handle($message);
    }

    /**+
     * Maps from symfony validation objects.
     *
     * @param ConstraintViolationListInterface $symfonyViolations
     *
     * @return Violation[]
     */
    private function mapFromSymfonyViolations(ConstraintViolationListInterface $symfonyViolations): array
    {
        $violations = [];

        foreach ($symfonyViolations as $symfonyViolation) {
            $violations[] = new Violation(
                $symfonyViolation->getPropertyPath(),
                $symfonyViolation->getMessageTemplate(),
                $this->mapFromSymfonyParameters($symfonyViolation->getParameters())
            );
        }

        return $violations;
    }

    /**
     * Remove template characters from parameter names.
     *
     * @param mixed[] $symfonyParameters
     *
     * @return mixed[]
     */
    private function mapFromSymfonyParameters(array $symfonyParameters): array
    {
        $parameters = [];

        foreach ($symfonyParameters as $name => $value) {
            $nameWithoutTemplateCode = str_replace(
                ['{{ ', ' }}'],
                '',
                $name
            );

            $parameters[$nameWithoutTemplateCode] = $value;
        }

        return $parameters;
    }
}
