<?php

declare(strict_types=1);

namespace Gaming\Common\Domain\Integration;

use Gaming\Common\Domain\Exception\DomainException;
use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\MessageBroker\Event\MessageFailed;
use Gaming\Common\MessageBroker\Message;
use GamingPlatform\Api\Common\V1\CommonV1;

final class ReplyWithErrorResponseOnMessageFailedListener
{
    public function messageFailed(MessageFailed $event): void
    {
        if (!$event->throwable instanceof DomainException) {
            return;
        }

        $event->context->reply(
            new Message(
                CommonV1::ErrorResponseType,
                CommonV1::createErrorResponse()
                    ->setViolations(
                        $event->throwable->violations->map(
                            static fn(Violation $violation) => CommonV1::createErrorResponse_Violation()
                                ->setPropertyPath($violation->propertyPath)
                                ->setIdentifier($violation->identifier)
                                ->setParameters(
                                    array_map(
                                        static fn(ViolationParameter $parameter) => CommonV1
                                            ::createErrorResponse_Violation_Parameter()
                                            ->setName($parameter->name)
                                            ->setValue((string)$parameter->value),
                                        $violation->parameters
                                    )
                                )
                        )
                    )
                    ->serializeToString()
            )
        );

        $event->stopPropagation();
    }
}
