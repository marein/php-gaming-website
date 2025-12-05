<?php

declare(strict_types=1);

namespace Gaming\Common\Domain\Integration;

use Gaming\Common\Domain\Exception\DomainException;
use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\Domain\Exception\Violations;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RespondWithViolationsAsJsonOnKernelExceptionListener
{
    /**
     * @param array<string, int> $identifierToStatusCode
     */
    public function __construct(
        private readonly array $identifierToStatusCode,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof DomainException)) {
            return;
        }

        if ($event->getRequest()->getRequestFormat() !== 'json') {
            return;
        }

        $event->setResponse(
            new JsonResponse(
                [
                    'message' => $this->extractMessage($exception->violations),
                    'violations' => $this->transformViolationsToArray($exception->violations)
                ],
                $this->statusCodeFromFirstMatchingViolationName($exception)
            )
        );
    }

    private function extractMessage(Violations $violations): string
    {
        $identifier = $violations->first()->identifier ?? 'domain.exception';
        $parameters = [];
        foreach ($violations->first()->parameters ?? [] as $violationParameter) {
            $parameters['{{ ' . $violationParameter->name . ' }}'] = $violationParameter->value;
        }

        return $this->translator->trans($identifier, $parameters);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function transformViolationsToArray(Violations $violations): array
    {
        return $violations->map(
            static fn(Violation $violation): array => [
                'propertyPath' => $violation->propertyPath,
                'identifier' => $violation->identifier,
                'parameters' => array_map(
                    static fn(ViolationParameter $parameter): array => [
                        'name' => $parameter->name,
                        'value' => $parameter->value
                    ],
                    $violation->parameters
                )
            ]
        );
    }

    private function statusCodeFromFirstMatchingViolationName(DomainException $exception): int
    {
        foreach ($exception->violations as $violation) {
            if (array_key_exists($violation->identifier, $this->identifierToStatusCode)) {
                return $this->identifierToStatusCode[$violation->identifier];
            }
        }

        return Response::HTTP_BAD_REQUEST;
    }
}
