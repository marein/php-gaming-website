<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration;

use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Violation;
use Gaming\Common\Bus\ViolationParameter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ApplicationExceptionToJsonListener
{
    /**
     * @var array<string, int>
     */
    private array $identifierToStatusCode;

    /**
     * @param array<string, int> $identifierToStatusCode
     */
    public function __construct(array $identifierToStatusCode)
    {
        $this->identifierToStatusCode = $identifierToStatusCode;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof ApplicationException)) {
            return;
        }

        if ($event->getRequest()->getRequestFormat() !== 'json') {
            return;
        }

        $event->setResponse(
            new JsonResponse(
                $this->transformViolationsToArray($exception),
                $this->statusCodeFromFirstMatchingViolationName($exception)
            )
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function transformViolationsToArray(ApplicationException $applicationException): array
    {
        return array_map(
            static fn(Violation $violation): array => [
                'propertyPath' => $violation->propertyPath(),
                'identifier' => $violation->identifier(),
                'parameters' => array_map(
                    static fn(ViolationParameter $parameter): array => [
                        'name' => $parameter->name(),
                        'value' => $parameter->value()
                    ],
                    $violation->parameters()
                )
            ],
            $applicationException->violations()
        );
    }

    private function statusCodeFromFirstMatchingViolationName(ApplicationException $exception): int
    {
        foreach ($exception->violations() as $violation) {
            if (array_key_exists($violation->identifier(), $this->identifierToStatusCode)) {
                return $this->identifierToStatusCode[$violation->identifier()];
            }
        }

        return Response::HTTP_BAD_REQUEST;
    }
}
