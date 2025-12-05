<?php

declare(strict_types=1);

namespace Gaming\Common\Domain\Integration;

use Gaming\Common\Domain\Exception\DomainException;
use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\Domain\Exception\Violations;
use Gaming\Common\MessageBroker\Event\MessageFailed;
use Gaming\Common\MessageBroker\Message;
use GamingPlatform\Api\Common\V1\ErrorResponse;

final class ReplyWithErrorResponseOnMessageFailedListener
{
    public function messageFailed(MessageFailed $event): void
    {
        if (!$event->throwable instanceof DomainException) {
            return;
        }

        $event->context->reply(
            new Message(
                'Common.ErrorResponse',
                new ErrorResponse()
                    ->setViolations($this->mapViolations($event->throwable->violations))
                    ->serializeToString()
            )
        );

        $event->stopPropagation();
    }

    /**
     * @return ErrorResponse\Violation[]
     */
    private function mapViolations(Violations $violations): array
    {
        return $violations->map(
            fn(Violation $violation) => new ErrorResponse\Violation()
                ->setPropertyPath($violation->propertyPath)
                ->setIdentifier($violation->identifier)
                ->setParameters($this->mapParameters($violation->parameters))
        );
    }

    /**
     * @param ViolationParameter[] $parameters
     *
     * @return ErrorResponse\Violation\Parameter[]
     */
    private function mapParameters(array $parameters): array
    {
        return array_map(
            static fn(ViolationParameter $parameter) => new ErrorResponse\Violation\Parameter()
                ->setName($parameter->name)
                ->setValue((string)$parameter->value),
            $parameters
        );
    }
}
