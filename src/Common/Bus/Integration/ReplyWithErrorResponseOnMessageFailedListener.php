<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration;

use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Violation;
use Gaming\Common\Bus\ViolationParameter;
use Gaming\Common\MessageBroker\Event\MessageFailed;
use Gaming\Common\MessageBroker\Message;
use GamingPlatform\Api\Common\V1\ErrorResponse;

final class ReplyWithErrorResponseOnMessageFailedListener
{
    public function messageFailed(MessageFailed $event): void
    {
        if (!$event->throwable instanceof ApplicationException) {
            return;
        }

        $event->context->reply(
            new Message(
                'Common.ErrorResponse',
                new ErrorResponse()
                    ->setViolations($this->mapViolations($event->throwable->violations()))
                    ->serializeToString()
            )
        );

        $event->stopPropagation();
    }

    /**
     * @param Violation[] $violations
     *
     * @return ErrorResponse\Violation[]
     */
    private function mapViolations(array $violations): array
    {
        return array_map(
            fn(Violation $violation) => new ErrorResponse\Violation()
                ->setPropertyPath($violation->propertyPath())
                ->setIdentifier($violation->identifier())
                ->setParameters($this->mapParameters($violation->parameters())),
            $violations
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
                ->setName($parameter->name())
                ->setValue((string)$parameter->value()),
            $parameters
        );
    }
}
