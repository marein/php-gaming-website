<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Symfony;

use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Violation;
use Gaming\Common\Bus\ViolationParameter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * This class transforms an ApplicationException to a json response.
 */
final class TransformApplicationExceptionListener
{
    /**
     * @var array<string, int>
     */
    private array $identifierToStatusCode;

    /**
     * TransformApplicationExceptionListener constructor.
     *
     * @param array<string, int> $identifierToStatusCode
     */
    public function __construct(array $identifierToStatusCode)
    {
        $this->identifierToStatusCode = $identifierToStatusCode;
    }

    /**
     * Sets the json representation of violations as a response.
     *
     * @param ExceptionEvent $event
     */
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
     * Transforms the violations from an ApplicationException to a serializable array.
     *
     * @param ApplicationException $applicationException
     *
     * @return array
     */
    private function transformViolationsToArray(ApplicationException $applicationException): array
    {
        return array_map(
            static fn(Violation $violation): array => [
                'propertyPath' => $violation->propertyPath(),
                'identifier'   => $violation->identifier(),
                'parameters'   => array_map(
                    static fn(ViolationParameter $parameter): array => [
                        'name'  => $parameter->name(),
                        'value' => $parameter->value()
                    ],
                    $violation->parameters()
                )
            ],
            $applicationException->violations()
        );
    }

    /**
     * Finds the status code that matches the map, otherwise 400.
     *
     * @param ApplicationException $exception
     *
     * @return int
     */
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
